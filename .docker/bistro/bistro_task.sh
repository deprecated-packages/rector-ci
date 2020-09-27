#!/bin/bash
echo "I got these arguments: \$@"
echo "stderr is also logged" 1>&2
echo "done" > "\$2"  # Report the task status to Bistro via a named pipe
