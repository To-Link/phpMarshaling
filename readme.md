# Description

This library is using for Updating Database by local cache data.

This works on Linux.

## As a message queue

It can be used for local message queue.

This is based on sqlite3 database, so it can lost some works. 
if you don't want to lost your tasks, then you have to yours tasks based from file.
and make some insert and error recovery logic for registering queue from file.

(Base file is like some csv or excel or img file deleted after inserting queue.)
