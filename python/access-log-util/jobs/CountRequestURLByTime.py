from jobs.AbstractJob import AbstractJob


class CountRequestURLByTime(AbstractJob):
    __data = {}

    def __init__(self, filename, time_type, begin_time, end_time):
        AbstractJob.__init__(self, filename, begin_time, end_time)
        if 'minute' == time_type:
            self.time_length = 17
        elif 'hour' == time_type:
            self.time_length = 14

    def _get_data(self, token):
        time = token[0][0:self.time_length]
        url = token[2]
        host = (url.split("?"))[0]
        if time not in self.__data:
            self.__data[time] = {}
        if host not in self.__data[time]:
            self.__data[time][host] = 1
        else:
            self.__data[time][host] += 1

    def output(self):
        keys = []

        for time in self.__data:
            for key in self.__data[time]:
                if key not in keys:
                    keys.append(key)
        key_list = self.__data.keys()
        key_list.sort()
        for key in keys:
            print "%s\t" % key,

        print
        for time in key_list:
            print "%s\t" % time,
            for key in keys:
                if key not in self.__data[time]:
                    print "0\t",
                else:
                    print "%d\t" % self.__data[time][key],
            print
