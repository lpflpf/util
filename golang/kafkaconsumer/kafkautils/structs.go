package kafkautils

type Callback func(msg string) error

// consumer 结构体
type ConsumerConfig struct {
	Topics          []string
	GroupName       string
	BrokerList      []string
	Concurrency     int
	QueueBuffer     int
	ClientId string
}
