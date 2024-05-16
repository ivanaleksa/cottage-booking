CREATE TABLE cottage_house (
	cottage_id serial PRIMARY KEY,
	cottage_name varchar(255),
	cottage_address varchar(500),
	cottage_description varchar(2000)
);

CREATE TABLE cottage_booking (
	booking_id serial PRIMARY KEY,
    cottage_id INT REFERENCES public.cottage_house (cottage_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
	client_name varchar(255),
	client_phone_number varchar(30),
	booking_start_at date,
	booking_end_at date,
	booking_confirmation_date date
);

CREATE TABLE admins (
    admin_id serial PRIMARY KEY,
    admin_login varchar(50),
    admin_password text
);

ALTER TABLE admins ADD token text;
