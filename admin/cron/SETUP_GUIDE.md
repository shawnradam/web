# Automated Maintenance & Cleanup Setup

## Windows Task Scheduler Setup

To run the automated cleanup every 30 days at 3 AM:

1. **Open Task Scheduler**
   - Press `Win + R`
   - Type `taskschd.msc`
   - Press Enter

2. **Create New Task**
   - Click "Create Task" (not "Create Basic Task")
   - Name: `Website Database Cleanup`
   - Description: `Automated database cleanup and maintenance`
   - Select "Run whether user is logged on or not"
   - Check "Run with highest privileges"

3. **Triggers Tab**
   - Click "New"
   - Begin the task: `On a schedule`
   - Settings: `Monthly`
   - Months: `Select all`
   - Days: `1` (first day of month)
   - Start time: `3:00:00 AM`
   - Click OK

4. **Actions Tab**
   - Click "New"
   - Action: `Start a program`
   - Program/script: `C:\xampp\php\php.exe`
   - Add arguments: `C:\xampp\htdocs\projects\shawnradam\tradvisor\advisor\admin\cron\database_cleanup.php`
   - Click OK

5. **Conditions Tab**
   - Uncheck "Start the task only if the computer is on AC power"
   - Check "Wake the computer to run this task"

6. **Settings Tab**
   - Check "Allow task to be run on demand"
   - Check "Run task as soon as possible after a scheduled start is missed"
   - Click OK

## Manual Testing

To test the cleanup manually:

```bash
cd C:\xampp\htdocs\projects\shawnradam\tradvisor\advisor\admin\cron
php database_cleanup.php
```

## What Gets Cleaned

- Login attempts older than 30 days
- Verification codes older than 7 days
- Expired sessions
- Read feedback older than 90 days
- Database optimization

## Maintenance Mode

- Activates automatically during cleanup
- Lasts approximately 2 hours (3 AM - 5 AM)
- Shows professional maintenance page
- Auto-refreshes every 60 seconds

## Logs

Check cleanup logs at:
```
C:\xampp\htdocs\projects\shawnradam\tradvisor\advisor\admin\cron\cleanup.log
```
