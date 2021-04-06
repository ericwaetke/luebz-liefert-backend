use crate::schema::subscribed_categories;
use crate::schema::subscribed_companies;

#[derive(Queryable)]
pub struct CategorySubscription {
    pub id: i32,
    pub user_id: i32,
    pub category_id: i32,
    pub date: chrono::NaiveDateTime
}

#[juniper::object(description = "Queries a single subscribed category")]
impl CategorySubscription{
    pub fn id (&self) -> i32 {
        self.id
    }

    pub fn user_id(&self) -> i32 {
        self.user_id
    }

    pub fn category_id(&self) -> i32 {
        self.category_id
    }

    pub fn date(&self) -> chrono::NaiveDateTime {
        self.date
    }
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "subscribed_categories"]
pub struct NewCategorySubscription {
    pub user_id: i32,
    pub category_id: i32,
}

#[derive(Queryable)]
pub struct CompanySubscription {
    pub id: i32,
    pub user_id: i32,
    pub company_id: i32,
    pub date: chrono::NaiveDateTime
}

#[juniper::object(description = "Queries a single subscribed company")]
impl CompanySubscription{
    pub fn id (&self) -> i32 {
        self.id
    }

    pub fn user_id(&self) -> i32 {
        self.user_id
    }

    pub fn company_id(&self) -> i32 {
        self.company_id
    }

    pub fn date(&self) -> chrono::NaiveDateTime {
        self.date
    }
}

#[derive(juniper::GraphQLInputObject, Insertable)]
#[table_name = "subscribed_companies"]
pub struct NewCompanySubscription {
    pub user_id: i32,
    pub company_id: i32,
}

#[derive(Queryable)]
pub struct SubscriptionDeleteSuccess{
    pub success: bool
}

#[juniper::object(description = "Queries a single subscribed company")]
impl SubscriptionDeleteSuccess {
    pub fn success(&self) -> bool {
        self.success    
    }
}