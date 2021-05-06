package main

import (
	"github.com/Shopify/sarama"
	"github.com/lpflpf/kafkaconsumer/kafkautils"
	"math/rand"
	"os"
	"os/signal"
	"sync"
	"sync/atomic"
	"syscall"
	"time"
)

func main() {
	conf := &kafkautils.ConsumerConfig{
		Topics:      []string{""},
		GroupName:   "engineMsgTest",
		BrokerList:  []string{""},
		Concurrency: 5000,
		QueueBuffer: 100,
		ClientId:    "go_test",
	}
	queues, consumerShutdown := kafkautils.CreateClientConsumerHighLevel(conf)
	closed := make(chan struct{})
	wg := sync.WaitGroup{}
	var count int32

	go func() {
		ch := time.Tick(time.Second)
		for {
			<-ch
			//println(atomic.LoadInt32(&count))
			atomic.StoreInt32(&count, 0)
		}
	}()

	//fmt.Println(len(queues))
	for i := 0; i < len(queues); i++ {
		wg.Add(1)
		go func(queue chan *kafkautils.CMessage) {
			defer wg.Done()
			for {
				select {
				case message := <-queue:
					//	time.Sleep(100 * time.Millisecond)
					time.Sleep(time.Duration(rand.Int()%20) * time.Millisecond)
					message.MarkMessage()
					atomic.AddInt32(&count, 1)
				case <-closed:
					return
				}
			}
		}(queues[i])
	}

	sigterm := make(chan os.Signal, 1)
	signal.Notify(sigterm, syscall.SIGINT, syscall.SIGTERM)
	select {
	case <-sigterm:
		close(consumerShutdown)
		close(closed)
		time.Sleep(100 * time.Millisecond)
		sarama.Logger.Println("terminating: via signal")
		wg.Wait()
	}
}
