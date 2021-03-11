extern crate dotenv;

use std::env;

use diesel::pg::PgConnection;
use diesel::prelude::*;
use dotenv::dotenv;

use juniper::{EmptyMutation, RootNode};

use crate::schema::members;

#[derive(Queryable)]
struct Member {
	pub id: i32,
	pub name: String,
	pub knockouts: i32,
	pub team_id: i32
}

#[juniper::object(description = "A member of a team")]
impl Member {
	pub fn id(&self) -> i32 {
		self.id  
	}

	pub fn name(&self) -> &str {
		self.name.as_str()
	}

	pub fn knockouts(&self) -> i32{
		self.knockouts
	}

	pub fn team_id(&self) -> i32 {
		self.team_id
	}
}


// User Table

// use crate::schema::users;

#[derive(Queryable)]
struct User {
	pub id: i32,
	pub name: String,
	pub knockouts: i32,
	pub team_id: i32
}

#[juniper::object(description = "A single User")]
impl User {
	pub fn id(&self) -> i32 {
		self.id  
	}

	pub fn name(&self) -> &str {
		self.name.as_str()
	}

	pub fn knockouts(&self) -> i32{
		self.knockouts
	}

	pub fn team_id(&self) -> i32 {
		self.team_id
	}
}

// use crate::schema::teams;

// #[derive(Queryable)]
// pub struct Team {
// 	pub id: i32,
// 	pub name: String,
// }

// #[juniper::object(description = "A team of members")]
// impl Team {
// 	pub fn id(&self) -> i32 {
// 		self.id
// 	}

// 	pub fn name(&self) -> &str {
// 		self.name.as_str()
// 	}

// 	pub fn members(&self) -> Vec<Member> {
// 		use crate::schema::members::dsl::*;
// 		let connection = establish_connection();
// 		members
// 			.filter(team_id.eq(self.id))
// 			.limit(100)
// 			.load::<Member>(&connection)
// 			.expect("Error loading members")
// 	}
// }

pub enum Category {
	Einzelhandel,
	Gastronomie,
	Dienstleistung,
	Sonstiges
}

fn printCategory(category: Category) -> String {
	match category{
		Einzelhandel => String::from("Einzelhandel"),
		Gastronomie => String::from("Gastronomie"),
		Dienstleistung => String::from("Dienstleistung"),
		Sonstiges => String::from("Sonstiges")
	}
}

pub struct Company {
	pub id: i32,
	pub name: String,
	pub category: Category,
	pub tel: String,
	pub mail: String,
	pub web: String,
	pub url: String,
	pub approved: bool
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
		printCategory(self.category).as_str()
	}

	pub fn tel(&self) -> &str {
        &self.tel.as_str()
	}

	pub fn mail(&self) -> &str {
		&self.mail.as_str()
	}

	pub fn web(&self) -> &str {
		&self.web.as_str()
	}

	pub fn url(&self) -> &str {
		&self.url.as_str()
	}

	pub fn approved(&self) -> &bool {
		&self.approved
	}

	pub fn subscribers() -> Vec<User> {
		// use crate::schema::members::dsl::*;
		// let connection = establish_connection();
		// members
		// 	.filter(team_id.eq(self.id))
		// 	.limit(100)
		// 	.load::<Member>(&connection)
		// 	.expect("Error loading members")
		vec![]
	}

	pub fn subscriber_count() -> i32 {
		12
	}
}


pub struct QueryRoot;

fn establish_connection() -> PgConnection {
	dotenv().ok();
	// let database_url = env::var("DATABASE_URL").expect("DATABASE_URL must be set");
	let database_url = "postgres://postgres:root@localhost/graph-ql";
	PgConnection::establish(&database_url).expect(&format!("Error connecting to {}", database_url))
}

#[juniper::object]
impl QueryRoot {
	fn members() -> Vec<Member> {
		use crate::schema::members::dsl::*;
		let connection = establish_connection();
		members
			.limit(100)
			.load::<Member>(&connection)
			.expect("Error loading members")
	}
	fn teams() -> Vec<Team> {
		use crate::schema::teams::dsl::*;
		let connection = establish_connection();
		teams
			.limit(100)
			.load::<Team>(&connection)
			.expect("Error loading teams")
	}
}


pub struct MutationRoot;

#[juniper::object]
impl MutationRoot {
	fn create_member(data: NewMember) -> Member {
		let connection = establish_connection();
		diesel::insert_into(members::table)
		.values(&data)
		.get_result(&connection)
		.expect("Error saving new post")
	}
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "members"]
	pub struct NewMember {
	pub name: String,
	pub knockouts: i32,
	pub team_id: i32,
}


pub type Schema = RootNode<'static, QueryRoot, MutationRoot>;

pub fn create_schema() -> Schema {
	Schema::new(QueryRoot {}, MutationRoot {})
}