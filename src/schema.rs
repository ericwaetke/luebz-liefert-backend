table! {
    categories (id) {
        id -> Int8,
        category_name -> Nullable<Varchar>,
    }
}

table! {
    companies (id) {
        id -> Int8,
        name -> Nullable<Varchar>,
        category -> Nullable<Varchar>,
        phone -> Nullable<Varchar>,
        mail -> Nullable<Varchar>,
        web -> Nullable<Varchar>,
        description -> Nullable<Text>,
        whatsapp -> Nullable<Int8>,
        approved -> Bool,
    }
}

table! {
    login_tokens (id) {
        id -> Int8,
        account_id -> Int8,
        token -> Varchar,
        date -> Date,
    }
}

table! {
    subscribed_categories (id) {
        id -> Int8,
        account_id -> Nullable<Int8>,
        category_id -> Nullable<Int8>,
    }
}

table! {
    subscribed_companies (id) {
        id -> Int8,
        account_id -> Nullable<Int8>,
        company_id -> Nullable<Int8>,
        date -> Nullable<Date>,
    }
}

table! {
    subscriptions (id) {
        id -> Int8,
        user_id -> Nullable<Int8>,
        #[sql_name = "type"]
        type_ -> Nullable<Varchar>,
        date -> Nullable<Date>,
        adress -> Nullable<Varchar>,
        auth -> Nullable<Varchar>,
        p256dh -> Nullable<Varchar>,
    }
}

table! {
    users (id) {
        id -> Int8,
        email -> Nullable<Varchar>,
        password -> Nullable<Varchar>,
        name -> Nullable<Varchar>,
        account_type -> Nullable<Varchar>,
        unique_identifier -> Nullable<Varchar>,
        register_date -> Nullable<Date>,
        varified -> Nullable<Bool>,
        last_action_date -> Nullable<Date>,
        company -> Nullable<Int8>,
    }
}

joinable!(subscribed_categories -> categories (category_id));

allow_tables_to_appear_in_same_query!(
    categories,
    companies,
    login_tokens,
    subscribed_categories,
    subscribed_companies,
    subscriptions,
    users,
);