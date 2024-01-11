#!/bin/bash
echo 'run after_install.sh: ' >> /home/ec2-user/superban/deploy.log

echo 'cd /home/ec2-user/superban' >> /home/ec2-user/superban/deploy.log
cd /home/ec2-user/superban >> /home/ec2-user/superban/deploy.log

echo 'composer install' >> /home/ec2-user/superban/deploy.log 
composer install >> /home/ec2-user/superban/deploy.log
