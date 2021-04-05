table! {
    categories (id) {
        id -> Integer,
        name -> Varchar,
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
        user_id -> Integer,
        category_id -> Integer,
        date -> Timestamp,
    }
}

table! {
    subscribed_companies (id) {
        id -> Integer,
        user_id -> Integer,
        company_id -> Integer,
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
        company_id -> Nullable<Integer>,
    }
}

table! {
    news (id) {
        id -> Int4,
        company_id -> Int4,
        date -> Timestamptz,
        titel -> Varchar,
        content -> Text,
    }
}

joinable!(users -> companies (company_id));

joinable!(subscribed_companies -> users (user_id));
joinable!(subscribed_companies -> companies (company_id));

joinable!(subscribed_categories -> categories (category_id));
joinable!(subscribed_categories -> users (user_id));

allow_tables_to_appear_in_same_query!(
    categories,
    companies,
    login_tokens,
    subscribed_categories,
    subscribed_companies,
    subscriptions,
    users,
);
