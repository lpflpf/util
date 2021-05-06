package export

import (
	"bytes"
	"encoding/csv"
	"fmt"
	"golang.org/x/text/encoding/simplifiedchinese"
	"golang.org/x/text/transform"
	"log"
	"os"
)

var Logger StdLogger = log.New(ioutil.Discard, "[export] ", log.LstdFlags)

func Csv(title []string, data [][]string) (*bytes.Buffer, error) {
	content := bytes.NewBufferString("")
	writer := csv.NewWriter(transform.NewWriter(content, simplifiedchinese.GBK.NewEncoder()))

	if len(title) > 0 {
		if err := writer.Write(title); err != nil {
			Logger.Printf("write title faield. Title size: %d. Error: %v", len(title), err)
			return nil, err
		}
	}
	if err := writer.WriteAll(data); err != nil {
		Logger.Printf("write content failed. Content size: %d. Error: %v", len(data), err)
		return nil, err
	}
	return content, nil
}

func Csv2File(title []string, data [][]string, filename string) error {
	_, err := os.Stat(filename)
	if os.IsExist(err) {
		title = []string{}
	}

	reader, err := Csv(title, data)
	if err != nil {
		return err
	}
	return WriteFile(filename, reader)
}
