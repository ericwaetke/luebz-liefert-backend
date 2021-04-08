use diesel::prelude::*;

use crate::schema::users;

use super::establish_connection;
use super::company::Company;
use super::category::Category;

#[derive(Queryable, Clone)]
// #[belongs_to(Company)]
pub struct User {
	pub id: i32,
	pub email: Option<String>,
	pub password: Option<String>,
	pub name: Option<String>,
	pub account_type: Option<String>,
	pub unique_identifier: String,
	pub register_date: chrono::NaiveDateTime,
	pub verified: bool,
	pub last_action_date: chrono::NaiveDateTime,
	pub company: Option<i32>,
}

#[juniper::object(description = "Queries a single user")]
impl User {
	pub fn id(&self) -> i32 {
		self.id
	}

	pub fn email(&self) -> &str {
		match &self.email {
			Some(val) => val,
			None => "",
		}
	}

	pub fn email(&self) -> &str {
		match &self.email {
			Some(val) => val,
			None => "",
		}
	}

	pub fn password(&self) -> &str {
		match &self.password {
			Some(val) => val,
			None => "",
		}
	}

	pub fn name(&self) -> &str {
		match &self.name {
			Some(val) => val,
			None => "",
		}
	}

	pub fn account_type(&self) -> &str {
		match &self.account_type {
			Some(val) => val,
			None => "",
		}
	}

	pub fn unique_identifier(&self) -> String {
		self.unique_identifier.to_string()
	}

	pub fn register_date(&self) -> String {
		self.register_date.to_string()
	}

	pub fn verified(&self) -> bool {
		self.verified
	}

	pub fn last_action_date(&self) -> String {
		self.last_action_date.to_string()
	}

	pub fn subscribed_companies(&self) -> Vec<Company> {
		use crate::schema::subscribed_companies::dsl::*;
		let connection = establish_connection();
		subscribed_companies
			.filter(user_id.eq(&self.id))
			.inner_join(crate::schema::companies::table)
			.select((crate::schema::companies::id, crate::schema::companies::name, crate::schema::companies::category, crate::schema::companies::phone, crate::schema::companies::mail, crate::schema::companies::web, crate::schema::companies::description, crate::schema::companies::whatsapp, crate::schema::companies::approved))
			.limit(100)
			.load::<Company>(&connection)
			.expect("Error could not load users")
	}

	pub fn subscribed_categories(&self) -> Vec<Category> {
		use crate::schema::subscribed_categories::dsl::*;
		let connection = establish_connection();
		subscribed_categories
			.filter(user_id.eq(&self.id))
			.inner_join(crate::schema::categories::table)
			.select((crate::schema::categories::id, crate::schema::categories::name))
			.limit(100)
			.load::<Category>(&connection)
			.expect("Error could not load users")
	}

	pub fn company(&self) -> Option<Company> {
		let company_id = self.company;

		match company_id {
			Some(company_id) => {
				use crate::schema::companies::dsl::*;
				let connection = establish_connection();
				companies
					.filter(id.eq(company_id))
					.limit(1)
					.first::<Company>(&connection)
					.optional()
					.expect("Error could not load companies")
			}
			None => None
		}
	}
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "users"]
pub struct NewUser {
	pub id: i32,
	pub email: Option<String>,
	pub password: Option<String>,
	pub name: Option<String>,
	pub account_type: Option<String>,
	pub unique_identifier: String,
}