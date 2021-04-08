extern crate dotenv;

use diesel::pg::PgConnection;
use diesel::prelude::*;

use dotenv::dotenv;
use std::env;

use juniper::{RootNode};



mod company;
mod user;
mod post;
mod category;
mod subscription;

use company::*;
use user::*;
use post::*;
use category::*;
use subscription::*;

pub fn establish_connection() -> PgConnection {
	dotenv().ok();
	let database_url = env::var("DATABASE_URL").expect("DATABASE_URL must be set");
	PgConnection::establish(&database_url).expect(&format!("Error connecting to {}", database_url))
}
pub struct QueryRoot;

#[juniper::object]
impl QueryRoot {
	fn company(company_id: i32) -> Company {
		use crate::schema::companies::dsl::*;
		let connection = establish_connection();
		companies
			.filter(id.eq(company_id))
			.first::<Company>(&connection)
			.expect("Error could not load users")
	}

	fn all_companies() -> Vec<Company> {
		use crate::schema::companies::dsl::*;
		let connection = establish_connection();
		companies
			.limit(100)
			.load::<Company>(&connection)
			.expect("Error could not load companies")
	}
	
	fn user(user_id: i32) -> User {
		use crate::schema::users::dsl::*;
		let connection = establish_connection();
		users
			.filter(id.eq(user_id))
			.first::<User>(&connection)
			.expect("Error could not load users")
	}

	fn all_users() -> Vec<User> {
		use crate::schema::users::dsl::*;
		let connection = establish_connection();
		users
			.limit(100)
			.load::<User>(&connection)
			.expect("Error could not load users")
	}

	fn category(category_id: i32) -> Category {
		use crate::schema::categories::dsl::*;
		let connection = establish_connection();
		categories
			.filter(id.eq(category_id))
			.first::<Category>(&connection)
			.expect("Error could not load")
	}

	fn all_categories() -> Vec<Category> {
		use crate::schema::categories::dsl::*;
		let connection = establish_connection();
		categories
			.load::<Category>(&connection)
			.expect("Error could not load")
	}

	fn posts(posts_id: i32) -> Post {
		use crate::schema::posts::dsl::*;
		let connection = establish_connection();
		posts
			.filter(id.eq(posts_id))
			.first::<Post>(&connection)
			.expect("Error could not load")
	}

	fn all_posts() -> Vec<Post> {
		use crate::schema::posts::dsl::*;
		let connection = establish_connection();
		posts
			.load::<Post>(&connection)
			.expect("Error could not load")
	}
}

pub struct MutationRoot;

#[juniper::object]
impl MutationRoot {
	fn create_post(data: NewPost) -> Post {
		let connection = establish_connection();
		diesel::insert_into(crate::schema::posts::table)
			.values(&data)
			.get_result(&connection)
			.expect("Error saving new post")
	}

	fn create_category(data: NewCategory) -> Category {
		let connection = establish_connection();
		diesel::insert_into(crate::schema::categories::table)
			.values(&data)
			.get_result(&connection)
			.expect("Error saving new post")
	}
	
	fn add_category_subscription(data: NewCategorySubscription) -> CategorySubscription {
		let connection = establish_connection();
		diesel::insert_into(crate::schema::subscribed_categories::table)
			.values(&data)
			.get_result(&connection)
			.expect("Error saving new Category Subscription")
	}

	fn add_company_subscription(data: NewCompanySubscription) -> CompanySubscription {
		let connection = establish_connection();
		diesel::insert_into(crate::schema::subscribed_companies::table)
			.values(&data)
			.get_result(&connection)
			.expect("Error saving new Category Subscription")
	}

	fn delete_company_subscription(arg_id: i32) -> SubscriptionDeleteSuccess {
		use crate::schema::subscribed_companies::dsl::*;
		let connection = establish_connection();
		let res = diesel::delete(crate::schema::subscribed_companies::table.filter(id.eq(arg_id)))
			.execute(&connection)
			.expect("Error changing Company Subscription");

		SubscriptionDeleteSuccess {
			success: res != 0
		}
	}

	fn delete_category_subscription(arg_id: i32) -> SubscriptionDeleteSuccess {
		use crate::schema::subscribed_categories::dsl::*;
		let connection = establish_connection();
		let res = diesel::delete(crate::schema::subscribed_categories::table.filter(id.eq(arg_id)))
			.execute(&connection)
			.expect("Error changing Category Subscription");

		SubscriptionDeleteSuccess {
			success: res != 0
		}
	}

	fn add_company(data: NewCompany) -> Company {
		let connection = establish_connection();
		diesel::insert_into(crate::schema::companies::table)
			.values(&data)
			.get_result::<Company>(&connection)
			.expect("Error saving new Category Subscription")
	}
	
}

pub type Schema = RootNode<'static, QueryRoot, MutationRoot>;

pub fn create_schema() -> Schema {
	Schema::new(QueryRoot {}, MutationRoot {})
}
