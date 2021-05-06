package export

import (
	"fmt"
	"io"
	"os"
)

func WriteFile(filename string, buffer io.Reader) error {
	file, err := os.OpenFile(filename, os.O_WRONLY|os.O_TRUNC|os.O_CREATE, 0666)
	if err != nil {
		Logger.Printf("open file %s failed. Error: %v", filename, err)
		return err
	}
	defer func() { _ = file.Close() }()

	if _, err = io.Copy(file, buffer); err != nil {
		Logger.Printf("io.Copy failed, failname: %s. Error: %v", filename, err)
		return err
	}
	return nil
}
