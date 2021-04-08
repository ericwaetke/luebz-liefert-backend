# Lübz Liefert Backend

Todo:

- [ ] GraphQL Data Layer
- [ ] GraphQL Mutations
- [ ] Secure Token Generation
- [ ] Push Notifications: https://github.com/pimeys/rust-web-push

## Queries:

- [x] All Companies
- [x] One Company

- [x] Companies:
	- [x] Subcribed Users
	- [x] Subscriber Count
	- [x] Admin
	- [x] News

- [x] All Users
- [x] One User

- [x] Users:
	- [x] Subscribed Comanies
	- [x] Subscribed Categories
	
- [x] All Categories
- [x] One Category

- [x] Categories:
	- [x] Subscribed Users

- [x] All News
- [x] One News
	- [ ] Filtered By:	id and company


## Mutations

- [x] Post a News 
	- [x] Add Categories to that News-Post (needs to be done seperately)

- [x] Add Subscription
- [x] Change Company Subscription Status
- [x] Change Category Subscription Status

- [x] Add Company
- Update Company Information
- Approve a Company

- Change Personal Information
- Change Passwort
- Verify Email

- Login 
- Sign Up
- oAuth Login
- Cookie Login
- Generate & add token as verification metric
- Delete Token
- Check Token Validity
- Reset Password


- Send out notifications when new post is published
	- Send that to category & company subscibers
		- Send both with E-Mail or via Push Notification

	- Opt out of emails

- (QOL) Send regular email with weeks worth of news

### Emails
- Once new company registers
	- to the company email address
	- to the user that registered it
- send email when company gets approved

- send email if account gets approved