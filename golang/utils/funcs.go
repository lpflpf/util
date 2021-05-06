package utils

import (
	"encoding/json"
	"fmt"
	"runtime/debug"
	"strings"
)

func Dump(data ...interface{}) {
	stacks := debug.Stack()
	lines := strings.SplitN(string(stacks), "\n", 7)

	dumpRow := ""
	if len(lines) == 7 {
		dumpRow = strings.Trim(lines[6], "\n\t")
		tokens := strings.Split(dumpRow, " ")
		dumpRow = tokens[0]
	}
	for _, v := range data {
		fmt.Printf("\x1b[33m%s\x1b[0m\n", dumpRow)
		switch val := v.(type) {
		default:
			m, _ := json.MarshalIndent(v, "", "    ")
			fmt.Printf("\x1b[32m%T : \x1b[0m\n%s\n", v, string(m))
		case complex128:
			fmt.Printf("\x1b[32m%T : \x1b[0m(%f, %fi)\n", v, real(val), imag(val))
		case complex64:
			fmt.Printf("\x1b[32m%T : \x1b[0m(%f, %fi)\n", v, real(val), imag(val))
		case string:
			fmt.Printf("\x1b[32m%T : \x1b[0m%s\n", v, v)
		case int64, int32, int, int16, int8, uint16, uint32, uint64:
			fmt.Printf("\x1b[32m%T : \x1b[0m%d\n", v, v)
		case uint8:
			fmt.Printf("\x1b[32m%T : \x1b[0m%d (ascii : %c)\n", v, v, v)
		case float64, float32:
			fmt.Printf("\x1b[32m%T : \x1b[0m%f\n", v, v)
		case []byte:
			fmt.Printf("\x1b[32m%T : \x1b[0m\n%s\n", v, string(v.([]byte)))
		}
		fmt.Println()
	}
}
