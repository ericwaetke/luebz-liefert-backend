use diesel::prelude::*;

use crate::schema::news;

use super::company::Company;
use super::establish_connection;

#[derive(Queryable, Clone)]
pub struct News {
	id: i32,
	company_id: i32,
	date: chrono::NaiveDateTime,
	title: String,
	content: String,
}

#[juniper::object(description = "A News")]
impl News{
	pub fn id(&self) -> i32 {
		self.id
	}

	pub fn company(&self) -> Company {
		use crate::schema::companies::dsl::*;
		let connection = establish_connection();
		companies
			.filter(id.eq(self.company_id))
			.limit(1)
			.first::<Company>(&connection)
			.expect("Error could not load companies")
	}

	pub fn date(&self) -> chrono::NaiveDateTime {
		self.date
	}

	pub fn title(&self) -> &str {
		&self.title
	}

	pub fn content(&self) -> &str {
		&self.content
	}
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "news"]
pub struct NewsPost {
	company_id: i32,
	title: String,
	content: String,
}