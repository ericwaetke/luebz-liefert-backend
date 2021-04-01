extern crate dotenv;

// use std::env;

use diesel::pg::PgConnection;
use diesel::prelude::*;
use dotenv::dotenv;

use juniper::{EmptyMutation, RootNode};

use chrono::prelude::*;

// pub enum Category {
//     Einzelhandel,
//     Gastronomie,
//     Dienstleistung,
//     Sonstiges,
// }

// fn printCategory(category: Category) -> String {
//     match category {
//         Einzelhandel => String::from("Einzelhandel"),
//         Gastronomie => String::from("Gastronomie"),
//         Dienstleistung => String::from("Dienstleistung"),
//         Sonstiges => String::from("Sonstiges"),
//     }
// }

#[derive(Queryable)]
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
	pub company_id: Option<Company>,
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

	pub fn company_id(&self) -> Option<Company> {
		self.company_id
	}
}

#[derive(Queryable, Eq, Hash, PartialEq)]
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

	// pub fn subscribers() -> Vec<User> {
	// 	// use crate::schema::members::dsl::*;
	// 	// let connection = establish_connection();
	// 	// members
	// 	// 	.filter(team_id.eq(self.id))
	// 	// 	.limit(100)
	// 	// 	.load::<Member>(&connection)
	// 	// 	.expect("Error loading members")
	// 	vec![]
	// }

	pub fn subscriber_count(&self) -> i32 {
		// Company::subscriber_count()
		12
	}
}

pub struct QueryRoot;

fn establish_connection() -> PgConnection {
	dotenv().ok();
	// let database_url = env::var("DATABASE_URL").expect("DATABASE_URL must be set");
	let database_url = "postgres://postgres:root@localhost/luebz-liefert-database";
	PgConnection::establish(&database_url).expect(&format!("Error connecting to {}", database_url))
}

#[juniper::object]
impl QueryRoot {
	fn companies() -> Vec<Company> {
		use crate::schema::companies::dsl::*;
		let connection = establish_connection();
		companies
			.limit(100)
			.load::<Company>(&connection)
			.expect("Error could not load companies")
	}

	fn users() -> Vec<User> {
		// let join = crate::schema::users::table.inner_join(crate::schema::companies::table);
		let join = crate::schema::users::dsl::*;
		// use crate::schema::users::dsl::*;
		let connection = establish_connection();
		join
			.limit(100)
			.load::<User>(&connection)
			.expect("Error could not load users")
	}
}

pub struct MutationRoot;

// #[juniper::object]
// impl MutationRoot {
// 	fn create_member(data: NewMember) -> Member {
// 		let connection = establish_connection();
// 		diesel::insert_into(members::table)
// 		.values(&data)
// 		.get_result(&connection)
// 		.expect("Error saving new post")
// 	}
// }

// #[derive(juniper::GraphQLInputObject, Insertable)]
// #[table_name = "members"]
// 	pub struct NewMember {
// 	pub name: String,
// 	pub knockouts: i32,
// 	pub team_id: i32,
// }

pub type Schema = RootNode<'static, QueryRoot, EmptyMutation<()>>;

pub fn create_schema() -> Schema {
	Schema::new(QueryRoot {}, EmptyMutation::new())
}
