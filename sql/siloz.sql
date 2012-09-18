USE siloz;

CREATE TABLE users (
	`user_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`username` VARCHAR(50),
	`password` VARCHAR(100),
	`fullname` VARCHAR(100),
	`phone` VARCHAR(20),
	`email` VARCHAR(50),
	`address` VARCHAR(200),
	`zip_code` VARCHAR(10),
	`user_type` INT,
	`joined_date` TIMESTAMP DEFAULT current_timestamp,
	`photo_file` VARCHAR(100)
);

CREATE TABLE silos (
	`silo_id`  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,	
	`admin_id` INT,
	`name` VARCHAR(200),
	`shortname` VARCHAR(50),
	`silo_cat_id` INT,
	`paypal_account` VARCHAR(20),
	`org_name` VARCHAR(100),
	`title` VARCHAR(20),
	`phone_number` VARCHAR(20),
	`address` VARCHAR(200),
	`zip_code` VARCHAR(10),
	`longitude` FLOAT(10, 6),
	`latitude` FLOAT(10,6),
	`start_date` DATE,
	`end_date` DATE,
	`created_date` TIMESTAMP DEFAULT current_timestamp,
	`goal` INT,
	`purpose` VARCHAR(200),
	`description` TEXT,
	`admin_notice` TEXT,
	`photo_file` VARCHAR(100)	
);

CREATE TABLE items (
	`item_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,	
	`user_id` INT,
	`silo_id` INT,
	`title` VARCHAR(200),
	`price` FLOAT(10,2),
	`item_cat_id` INT,
	`description` TEXT,
	`status` VARCHAR(20),
	`link` VARCHAR(200) DEFAULT NULL,
	`photo_file_1` VARCHAR(20),
	`photo_file_2` VARCHAR(20),
	`photo_file_3` VARCHAR(20),
	`photo_file_4` VARCHAR(20),
	`added_date` TIMESTAMP DEFAULT current_timestamp,
	`sold_date` TIMESTAMP DEFAULT 0,
	`sent_date` TIMESTAMP DEFAULT 0,
	`received_date` TIMESTAMP DEFAULT 0,
	`deleted_date` TIMESTAMP DEFAULT 0		
);

CREATE TABLE donations (
	`donation_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` INT,
	`silo_id` INT,
	`amount` FLOAT(10,2),
	`status` VARCHAR(50),
	`ref` VARCHAR(20),
	`sent_date` TIMESTAMP DEFAULT 0,
	`received_date` TIMESTAMP DEFAULT 0,
	`deleted_date` TIMESTAMP DEFAULT 0
);

CREATE TABLE silo_membership (
	`silo_id` INT,
	`user_id` INT,
	`joined_date` TIMESTAMP DEFAULT current_timestamp
);

CREATE TABLE item_categories (
	`item_cat_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,	
	`category` VARCHAR(100)
);

INSERT INTO item_categories(`category`) VALUES ('Art');
INSERT INTO item_categories(`category`) VALUES ('Art / Craft Supplies');
INSERT INTO item_categories(`category`) VALUES ('Baby');
INSERT INTO item_categories(`category`) VALUES ('Bicycles');
INSERT INTO item_categories(`category`) VALUES ('Boats / Watercraft');
INSERT INTO item_categories(`category`) VALUES ('Business Supplies');
INSERT INTO item_categories(`category`) VALUES ('Businesses');
INSERT INTO item_categories(`category`) VALUES ('Cars and Trucks');
INSERT INTO item_categories(`category`) VALUES ('Clothing');
INSERT INTO item_categories(`category`) VALUES ('Computers');
INSERT INTO item_categories(`category`) VALUES ('Construction Supplies');
INSERT INTO item_categories(`category`) VALUES ('Electronic Media');
INSERT INTO item_categories(`category`) VALUES ('Electronics');
INSERT INTO item_categories(`category`) VALUES ('Everything (default)');
INSERT INTO item_categories(`category`) VALUES ('General / Other');
INSERT INTO item_categories(`category`) VALUES ('Home Furniture');
INSERT INTO item_categories(`category`) VALUES ('Home Decor');
INSERT INTO item_categories(`category`) VALUES ('Instruments / DJ / PA');
INSERT INTO item_categories(`category`) VALUES ('Jewelry');
INSERT INTO item_categories(`category`) VALUES ('Outdoor');
INSERT INTO item_categories(`category`) VALUES ('Photo / Video');
INSERT INTO item_categories(`category`) VALUES ('Portable Electronics');
INSERT INTO item_categories(`category`) VALUES ('Print');
INSERT INTO item_categories(`category`) VALUES ('Sporting Goods');
INSERT INTO item_categories(`category`) VALUES ('Stage / Film');
INSERT INTO item_categories(`category`) VALUES ('Tickets');
INSERT INTO item_categories(`category`) VALUES ('Tools');
INSERT INTO item_categories(`category`) VALUES ('Toys');
INSERT INTO item_categories(`category`) VALUES ('Vehicles / Parts');

CREATE TABLE silo_categories (
	`silo_cat_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`type` VARCHAR(20),
	`subtype` VARCHAR(50),
	`subsubtype` VARCHAR(50)
);

INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','College / Private Education','Books');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','College / Private Education','Field Trip / Trip Abroad');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','College / Private Education','Housing');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','College / Private Education','Tuition and Fees');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Anniversary');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Baby Shower');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Birthday');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Coming Out Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Father’s Day');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Graduation');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Honeymoon');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Mother’s Day');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Party (Other)');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Prom');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Religious/Holiday');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Retirement');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Sweet 16');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Thank You');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Event or Gift','Wedding');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Bail');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Creature Comfort');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Dream');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Funeral');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Home');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Legal');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Medical');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Relocation');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Tools');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Travel');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Vehicle');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Expense or Seed Money','Work');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Bills');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Creature Comfort');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Gift');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Reunion');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Summer Camp / Sports Camp');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Vacation');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Household / Family','Youth Expense');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Loan Assistance','Auto');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Loan Assistance','Mortgage');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Loan Assistance','Other');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Loan Assistance','Student Loans');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Public School','Arts, Sports, Club');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Personal','Public School','Field Trip / Trip Abroad');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Charity');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Operating Cost');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Adult Social, Interest, Sports Clubs / Meetups','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Charity');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Graduation');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Reunion');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Class / Dorm','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Library');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Metro / Civic','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Operating Costs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Neighborhood','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Arts, Sports, Clubs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Books or Academic');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Charity');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Field Trip / Trip Abroad');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Homecoming');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Housing');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Party or Dance');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Pep Rally');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','Student Loans');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Public School','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Charity');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Operating Costs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Religious NGO','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Operating Costs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Secular NGO / Non-Profit','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Charity');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Operating Costs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Reunion');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Sorority / Fraternity','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Cleanup');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Construction');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Event');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Fee');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Operating Costs');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Party');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Purchase');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','TBD or Ad Hoc or Excess Stuff');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Trip');
INSERT INTO silo_categories(`type`,`subtype`,`subsubtype`) VALUES('Community','Youth Sports, Social, Interest Clubs','Uniforms, Awards, Equipment');

