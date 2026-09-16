# Elite Event — MySQL Setup

## XAMPP quick setup

1. Put the `elite_event` folder inside `C:\xampp\htdocs\`.
2. Open XAMPP and start **Apache** and **MySQL**.
3. The project is configured for:
   - Host: `localhost`
   - Port: `3306`
   - Database: `elite_event`
   - User: `root`
   - Password: blank
4. Open `http://localhost/elite_event/`.

The PHP database layer will automatically create the MySQL database and tables if they do not exist.

### Alternative: phpMyAdmin

You can manually import `database/elite_event_mysql.sql` in phpMyAdmin. This creates:

- `users`
- `events`
- `registrations`
- `instagram_posts`

The important registration rule is:

```sql
UNIQUE KEY uq_event_user (event_id, user_id)
```

This guarantees that one account cannot register for the same event twice, even if someone tries to submit the request manually.

## Default admin

- Email: `admin@eliteevent.local`
- Password: `Admin@123`

Change this password after the first login in a real deployment.

## If your MySQL password is not blank

Edit `config/database.php`:

```php
'username' => 'root',
'password' => 'YOUR_MYSQL_PASSWORD',
```

## Google Form registration

Teachers/admins can paste the Google Form URL while creating/editing an event. Students must log in with a student account and can reserve/register only once per event. After the database registration succeeds, the Google Form is shown on the event page.

Ticket price, ticket quantity, checkout, payment, and revenue fields are not used by this version.
