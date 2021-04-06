use crate::schema::categories;

use diesel::prelude::*;

use super::establish_connection;
use super::user::User;

#[derive(Queryable, Clone)]
pub struct Category {
	pub id: i32,
	pub name: String,
}

#[juniper::object(description = "A Cagegory")]
impl Category {
	pub fn id(&self) -> i32 {
		self.id
	}

	pub fn name(&self) -> &str {
		&self.name
	}

	pub fn subscribers(&self) -> Vec<User> {
		use crate::schema::subscribed_categories::dsl::*;
		let connection = establish_connection();
		subscribed_categories
			.filter(category_id.eq(&self.id))
			.inner_join(crate::schema::users::table)
			.select((crate::schema::users::dsl::id, crate::schema::users::dsl::email, crate::schema::users::dsl::password, crate::schema::users::dsl::name, crate::schema::users::dsl::account_type, crate::schema::users::dsl::unique_identifier, crate::schema::users::dsl::register_date, crate::schema::users::dsl::verified, crate::schema::users::dsl::last_action_date, crate::schema::users::dsl::company_id))
			.limit(100)
			.load::<User>(&connection)
			.expect("Error could not load users")
	}
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "categories"]
pub struct NewCategory {
    pub name: String,
}