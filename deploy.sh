#!/bin/bash

sshpass -p "$1" ssh -o StrictHostKeyChecking=no -t ateliersjw@ssh.cluster027.hosting.ovh.net ; 'cd ~/www/wp-content/themes/mineursdefonds && git pull origin master'
