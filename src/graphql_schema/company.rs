use diesel::prelude::*;

use std::convert::TryFrom;

use super::establish_connection;
use super::user::User;
use super::news::News;

#[derive(Queryable, Clone)]
pub struct Company {
	pub id: i32,
	pub name: String,
	pub category: String, // I have the enum but don't know how that works with the database
	pub phone: Option<String>,
	pub mail: Option<String>,
	pub web: Option<String>,
	pub description: Option<String>,
	pub whatsapp: Option<String>,
	pub approved: bool,
}

#[juniper::object(description = "A Single Company")]
impl Company {
	pub fn id(&self) -> i32 {
		self.id
	}

	pub fn name(&self) -> &str {
		self.name.as_str()
	}

	pub fn category(&self) -> &str {
		self.category.as_str()
	}

	pub fn tel(&self) -> &str {
		match &self.phone {
			Some(val) => val,
			None => "",
		}
	}

	pub fn mail(&self) -> &str {
		match &self.mail {
			Some(val) => val,
			None => "",
		}
	}

	pub fn web(&self) -> &str {
		match &self.web {
			Some(val) => val,
			None => "",
		}
	}

	pub fn approved(&self) -> &bool {
		&self.approved
	}

	pub fn news(&self) -> Vec<News> {
		use crate::schema::news::dsl::*;
		let connection = establish_connection();
		news
			.filter(company_id.eq(self.id))
			.load::<News>(&connection)
			.expect("Could not load News")
	}

	pub fn subscribers(&self) -> Vec<User> {
		use crate::schema::subscribed_companies::dsl::*;
		let connection = establish_connection();
		subscribed_companies
			.filter(company_id.eq(&self.id))
			.inner_join(crate::schema::users::table)
			.select((crate::schema::users::dsl::id, crate::schema::users::dsl::email, crate::schema::users::dsl::password, crate::schema::users::dsl::name, crate::schema::users::dsl::account_type, crate::schema::users::dsl::unique_identifier, crate::schema::users::dsl::register_date, crate::schema::users::dsl::verified, crate::schema::users::dsl::last_action_date, crate::schema::users::dsl::company_id))
			.limit(100)
			.load::<User>(&connection)
			.expect("Error could not load users")
	}

	pub fn subscriber_count(&self) -> Option<i32> {
		use crate::schema::subscribed_companies::dsl::*;
		let connection = establish_connection();
		let subscribers: i64 = subscribed_companies
			.filter(company_id.eq(&self.id))
			.count()
			.get_result(&connection)
			.expect("Error could not load companies");

		match i32::try_from(subscribers) {
			Ok(n) => Some(n),
			Err(e) => None
		}
	}

	pub fn admin(&self) -> Option<User> {
		use crate::schema::users::dsl::*;
		let connection = establish_connection();
		users
			.filter(company_id.eq(&self.id))
			.first::<User>(&connection)
			.optional()
			.expect("Error could not load companies")
	}
}