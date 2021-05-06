from jobs.AbstractJob import AbstractJob


class CountRequestURL(AbstractJob):
    def __init__(self, filename, begin_time="", end_time=""):

        AbstractJob.__init__(self, filename, begin_time, end_time)

        self.__data = {}

    def _get_data(self, token):
        url = token[2]
        host = (url.split("?"))[0]

        if host in self.__data:
            self.__data[host] += 1
        else:
            self.__data[host] = 1

    def output(self):
        for (url, count) in self.__data.items():
            print "%s, %d" % (url, count)
