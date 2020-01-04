#!/bin/bash

sshpass -p "$1" ssh -o StrictHostKeyChecking=no -t root@felinn.org -p 1322 ; 'cd ~/src/mineursdefonds && git pull origin master'
