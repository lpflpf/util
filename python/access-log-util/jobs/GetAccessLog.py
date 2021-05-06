from jobs.AbstractJob import AbstractJob
import os
import datetime


class GetAccessLog:
    def __init__(self, filename, begin_time="", end_time=""):
        self.begin_time = begin_time
        self.end_time = end_time
        self.filename = filename
        self._file = open(filename, 'r')

    def output(self):
        file_size = self.get_file_size(self.filename)
        self.search_position(file_size, self.end_time)
        end = self._file.tell()
        self._file.seek(0,0)
        self.search_position(file_size, self.begin_time)

        for line in self._file:
            print line,
            if self._file.tell() > end:
                break
    
    def analysis_access_log(self):
        pass

    def get_file_size(self, filename):
        return os.path.getsize(filename)

    def search_position(self, file_size, times):
        if 0 == len(times):
            return

        offset_size = 1000000
        begin = datetime.datetime.strptime(times, '%Y%m%d %H:%M:%S')
        if file_size > offset_size:
            line = self._file.readline()
            line_time = self.get_time((line.split("\t"))[0])

            while line_time < begin:
                self._file.seek(offset_size, 1)
                self._file.readline()
                line = self._file.readline()
                if 0 == len(line):
                    self._file.seek(-offset_size, 1)
                    break
                line_time = self.get_time((line.split("\t"))[0])
                if line_time > begin:
                    self._file.seek(-offset_size, 1)

            if 0L != self._file.tell():
                line_width = 100
                while True:
                    self._file.seek(-line_width, 1)
                    str_len = len(self._file.readline())
                    if str_len > line_width:
                        line_width += 100
                        continue
                    break

        while True:
            line = self._file.readline()
            line_len = len(line)
            time = self.get_time((line.split("\t"))[0])

            if time >= begin:
                self._file.seek(-line_len, 1)
                break

    def get_time(self, str_time):
        return datetime.datetime.strptime(str_time, '%d/%b/%Y:%H:%M:%S +0800')
        #return datetime.datetime.strptime(str_time, '%Y-%m-%d %H:%M:%S')
