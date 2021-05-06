package export

import (
	"bytes"
	"html"
)

func Excel(title []string, data [][]string) *bytes.Buffer {
	arr := make([]bool, len(data[0])+1)
	arr[0] = true

	datas := [][]string{title}
	datas = append(datas, data...)

	return ExcelCommon(arr, datas)
}

func ExcelCommon(title []bool, data [][]string) *bytes.Buffer {
	buffer := bytes.NewBufferString("")
	buffer.WriteString(`<html xmlns:x="urn:schemas-microsoft-com:office:excel">`)
	buffer.WriteString(`<head><meta http-equiv="Content-type" content="text/html;charset=UTF-8" /></head>`)
	buffer.WriteString(`<style><!--
                    body,table{font-size:14px;}
                    table{border-collapse:collapse;}
                    th{padding:2px;border:1px solid #000;white-space:nowrap;background-color:#F2FAFF;}
                    td{padding:2px;border:1px solid #000;white-space:nowrap;}
                    --></style>`)
	buffer.WriteString(`<body><table border="1" cellspacing="0" cellpadding="0"><tbody>`)

	for i := 0; i < len(title); i++ {
		buffer.WriteString(`<tr>`)
		if title[i] {
			for _, val := range data[i] {
				buffer.WriteString(`<th style="background-color:#F2FAFF;white-space:nowrap;">`)
				buffer.WriteString(html.EscapeString(val))
				buffer.WriteString(`</th>`)
			}
		} else {
			for _, val := range data[i] {
				buffer.WriteString(`<td>`)
				buffer.WriteString(html.EscapeString(val))
				buffer.WriteString(`</td>`)
			}
		}
		buffer.WriteString(`</tr>`)
	}
	buffer.WriteString(`</tbody></table></body></html>`)

	return buffer
}

func Excel2FileCommon(title []bool, data [][]string, filename string) error {
	return WriteFile(filename, ExcelCommon(title, data))
}

func Excel2File(title []string, data [][]string, filename string) error {
	return WriteFile(filename, Excel(title, data))
}
