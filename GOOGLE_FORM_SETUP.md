# Elite Event — Google Form Registration Setup

## What was changed

- Removed ticket price, ticket quantity, checkout and revenue-related UI/code.
- Added `google_form_url` to the `events` database table.
- Added `registrations` table with a unique `(event_id, user_id)` constraint.
- Only student accounts can register for an event.
- A student account can register only once for the same event.
- Added Google Form URL field to the teacher/admin Create Event page.
- Event details page shows the Google Form after the account registration is recorded.
- Added student dashboard registration history.
- Added admin registration list.
- Added admin and teacher dashboards so the existing navigation links work.
- Converted the project database layer from SQLite to MySQL and included a ready-to-import MySQL schema.

## Create a Google Form

1. Open Google Forms.
2. Create a form for the event.
3. Add the questions you need, for example:
   - Full Name
   - Email
   - Phone Number
   - College
   - Department / Semester
   - Any event-specific questions
4. Click **Send**.
5. Copy the form URL or the URL from the Embed option.
6. Log in to Elite Event as a teacher/admin.
7. Open **Create Event** or edit an event.
8. Paste the URL into **Google Form registration URL**.
9. Save the event.

## Student registration flow

1. Student creates/logs into a student account.
2. Student opens an event.
3. Student clicks **Register with my account**.
4. The website creates one registration record for that student and event.
5. The Google Form appears on the same event page.
6. If the student returns later, the page shows **Already registered** and does not create another website registration.

## Important Google Forms setting

For stronger duplicate-response protection inside Google Forms itself, open the Google Form's **Settings** and consider enabling **Limit to 1 response**. Google may require respondents to sign in to a Google account for that setting.

The Elite Event database independently enforces one registration per Elite Event account, so repeated clicks on the website cannot create duplicate registration records.

## Database

This version uses **MySQL**. The schema is in:

`database/elite_event_mysql.sql`

The main tables are:

- `users`
- `events`
- `registrations`
- `instagram_posts`

The `events.google_form_url` field stores the form URL.

The `registrations` table stores:

- registration id
- event id
- student user id
- registration timestamp

The database has a UNIQUE constraint on `(event_id, user_id)`, so duplicate registrations are blocked at database level.

See `MYSQL_SETUP.md` for XAMPP/phpMyAdmin setup.
