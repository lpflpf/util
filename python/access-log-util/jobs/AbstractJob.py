#
# coding=utf-8
import os
import datetime
import abc

class AbstractJob:
    def __init__(self, filename, time_begin="", time_end=""):
        self._file = open(filename, 'r')
        self.search_begin_position(os.path.getsize(filename), time_begin)
        if "" == time_end:
            self.time_end = False
        else:
            self.time_end = datetime.datetime.strptime(time_end, '%Y%m%d %H:%M:%S')

    def analysis_access_log(self):
        if type(self.time_end) == bool:
            for line in self._file:
                token = line.split('\t')
                self._get_data(token)
        else:
            for line in self._file:
                token = line.split('\t')
                if self.get_time(token[0]) > self.time_end:
                    break
                self._get_data(token)

    @abc.abstractmethod
    def _get_data(self, token):
        pass

    @abc.abstractmethod
    def output(self):
        pass

    def search_begin_position(self, file_size, time_begin):
        if 0 == len(time_begin):
            return

        offset_size = 1000000  # 偏移量为1M
        begin = datetime.datetime.strptime(time_begin, '%Y%m%d %H:%M:%S')
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

    @staticmethod
    def get_time(str_time):
#        return datetime.datetime.strptime(str_time, '%d/%b/%Y:%H:%M:%S +0800')
        #2016-11-04 00:01:28
        return datetime.datetime.strptime(str_time, '%%Y-%b-%d %H:%M:%S')
