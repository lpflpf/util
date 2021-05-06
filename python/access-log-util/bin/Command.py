#
# coding=utf-8
import sys
import getopt
import os
from optparse import OptionParser
from optparse import OptionGroup

from jobs import CountRequestURL
from jobs import CountRequestURLByTime
from jobs import GetAccessLog


class Command:
    def __init__(self):
        pass

    @staticmethod
    def run():
        options = Command.get_options()

        begin_time = options.begin_time
        end_time = options.end_time
        filename = options.filename
        app = None

        if 'count_host' == options.type:
            app = CountRequestURL.CountRequestURL(filename, begin_time, end_time)
        if 'count_host_by_time' == options.type:
            app = CountRequestURLByTime.CountRequestURLByTime(filename, options.time_type, begin_time, end_time)
        if 'get_access_log' == options.type:
            app = GetAccessLog.GetAccessLog(filename, begin_time, end_time)

        if app is not None:
            app.analysis_access_log()
            app.output()
            return True
        return False

    @staticmethod
    def get_options():
        try:
            parser = OptionParser(usage="%prog [-b] [-e] -f filename", version="%prog 1.0.0")
            parser.add_option("-t", "--type",
                              action="store",
                              dest="type",
                              type='string',
                              default="",
                              help="analysis type, eg. count_host")
            parser.add_option("-f", "--filename",
                              action="store",
                              dest="filename",
                              help="log file name")
            parser.add_option("-a",
                              "--time-type",
                              action="store",
                              dest="time_type",
                              type="string",
                              default="minute",
                              help="time type for count request url by time.")

            time_group = OptionGroup(parser, "time options",
                                     """when set begin_time and end_time,
                                        it just analysis log in time [begin_time, end_time]""")
            time_group.add_option('-b', '--begin-time', action="store", dest='begin_time', default="",
                                  help="log begin time")
            time_group.add_option('-e', '--end-time', action="store", dest='end_time', default="", help="log end time")
            parser.add_option_group(time_group)

            (ret_options, args) = parser.parse_args()
            return ret_options

        except getopt.GetoptError:
            sys.exit()


