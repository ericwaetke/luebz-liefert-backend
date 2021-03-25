table! {
    categories (id) {
        id -> Integer,
        category_name -> Nullable<Varchar>,
    }
}

table! {
    companies (id) {
        id -> Integer,
        name -> Varchar,
        category -> Varchar,
        phone -> Nullable<Varchar>,
        mail -> Nullable<Varchar>,
        web -> Nullable<Varchar>,
        description -> Nullable<Text>,
        whatsapp -> Nullable<Varchar>,
        approved -> Bool,
    }
}

table! {
    login_tokens (id) {
        id -> Integer,
        account_id -> Integer,
        token -> Varchar,
        date -> Timestamp,
    }
}

table! {
    subscribed_categories (id) {
        id -> Integer,
        account_id -> Nullable<Integer>,
        category_id -> Nullable<Integer>,
    }
}

table! {
    subscribed_companies (id) {
        id -> Integer,
        account_id -> Nullable<Integer>,
        company_id -> Nullable<Integer>,
        date -> Timestamp,
    }
}

table! {
    subscriptions (id) {
        id -> Integer,
        user_id -> Nullable<Integer>,
        #[sql_name = "type"]
        type_ -> Nullable<Varchar>,
        date -> Timestamp,
        adress -> Nullable<Varchar>,
        auth -> Nullable<Varchar>,
        p256dh -> Nullable<Varchar>,
    }
}

table! {
    users (id) {
        id -> Integer,
        email -> Nullable<Varchar>,
        password -> Nullable<Varchar>,
        name -> Nullable<Varchar>,
        account_type -> Nullable<Varchar>,
        unique_identifier -> Varchar,
        register_date -> Timestamptz,
        verified -> Bool,
        last_action_date -> Timestamptz,
        company -> Nullable<Integer>,
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
