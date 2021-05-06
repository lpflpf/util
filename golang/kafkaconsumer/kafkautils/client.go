package kafkautils

import (
	"container/list"
	"context"
	"github.com/Shopify/sarama"
	"hash/fnv"
	"sync"
	"time"
)

// 创建 consumer high-level链接
func CreateClientConsumerHighLevel(conf *ConsumerConfig) ([]chan *CMessage, chan struct{}) {
	config := sarama.NewConfig()
	config.Version = sarama.V1_1_1_0
	config.Consumer.Fetch.Default = 8 * 1024 * 1024
	config.Consumer.Return.Errors = true
	config.Consumer.Offsets.CommitInterval = 1 * time.Second
	config.ClientID = conf.ClientId
	config.Consumer.Offsets.Initial = sarama.OffsetOldest

	ConsumerHighLevel, err := sarama.NewConsumerGroup(conf.BrokerList, conf.GroupName, config)
	if err != nil {
		panic(err)
	}

	consumer := Consumer{
		ready:     make(chan bool),
		config:    conf,
		chMessage: make([]chan *CMessage, conf.Concurrency),
	}
	for i := 0; i < len(consumer.chMessage); i++ {
		consumer.chMessage[i] = make(chan *CMessage, conf.QueueBuffer)
	}

	chShutdown := make(chan struct{})
	ctx := context.Background()
	go func() {
		for {
			select {
			case err = <-ConsumerHighLevel.Errors():
				if err != nil {
					sarama.Logger.Printf("[ERROR] Error: %s", err.Error())
				}
			case <-chShutdown:
				if err = ConsumerHighLevel.Close(); err != nil {
					sarama.Logger.Println("Error closing client: %v", err)
				}
			default:
				if err := ConsumerHighLevel.Consume(ctx, conf.Topics, &consumer); err != nil {
					sarama.Logger.Printf("[ERROR] Error from Consumer: %s", err.Error())
				}
				if ctx.Err() != nil {
					return
				}
				consumer.ready = make(chan bool)
			}
		}
	}()

	<-consumer.ready
	return consumer.chMessage, chShutdown
}

type CMessage struct {
	Message     *sarama.ConsumerMessage
	MarkMessage func()
}

// Consumer represents a Sarama consumer GroupName consumer
type Consumer struct {
	ready     chan bool
	config    *ConsumerConfig
	chMessage []chan *CMessage
	shutdown  chan struct{}
}

// Setup is run at the beginning of a new session, before ConsumeClaim
func (consumer *Consumer) Setup(sarama.ConsumerGroupSession) error {
	// Mark the consumer as ready
	close(consumer.ready)
	return nil
}

// Cleanup is run at the end of a session, once all ConsumeClaim goroutines have exited
func (consumer *Consumer) Cleanup(sarama.ConsumerGroupSession) error {
	return nil
}

func (consumer *Consumer) Sharding(message *sarama.ConsumerMessage) int {
	hashFunc := fnv.New32a()
	_, _ = hashFunc.Write(message.Key)
	return int(hashFunc.Sum32()) % consumer.config.Concurrency
}

type None struct{}

// ConsumeClaim must start a consumer loop of ConsumerGroupClaim's Messages().
func (consumer *Consumer) ConsumeClaim(session sarama.ConsumerGroupSession, claim sarama.ConsumerGroupClaim) (err error) {
	waitCommitQueue := list.New()
	waitCommitMap := make(map[int64]None, 100000)
	var mutex sync.Mutex

	for {
		select {
		case <-consumer.shutdown:
			return
		case message := <-claim.Messages():
			mutex.Lock()
			waitCommitQueue.PushBack(message)
			mutex.Unlock()
			consumer.chMessage[consumer.Sharding(message)] <- &CMessage{
				Message: message,
				MarkMessage: func() {
					mutex.Lock()
					defer mutex.Unlock()

					if waitCommitQueue.Front().Value.(*sarama.ConsumerMessage).Offset == message.Offset {
						waitCommitQueue.Remove(waitCommitQueue.Front())
						session.MarkMessage(message, "")
						for waitCommitQueue.Len() > 0 {
							item := waitCommitQueue.Front()
							offset := item.Value.(*sarama.ConsumerMessage).Offset

							if _, ok := waitCommitMap[offset]; !ok {
								break
							}
							delete(waitCommitMap, offset)
							session.MarkMessage(item.Value.(*sarama.ConsumerMessage), "")
							waitCommitQueue.Remove(item)
						}
					} else {
						waitCommitMap[message.Offset] = None{}
					}
				},
			}
		}
	}
}
