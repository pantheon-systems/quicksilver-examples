# Twilio SMS integration

Sign up for an account - https://www.twilio.com

Requires PHP Helper Library - download here - http://github.com/twilio/twilio-php

#### pantheon.yml

Add this snippet to enable 

```
# Commits: Notify team of new commit to master (dev)
  sync_code:
    after:
      - type: webphp
        description: send sms to site owner
        script: private/quicksilver/sms/twilio_deploy_notification.php
```
