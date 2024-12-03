CREATE TABLE users (
    id VARCHAR(255),
    name_ VARCHAR(100),
    password VARCHAR(255),
    PRIMARY KEY (id)
);

CREATE TABLE calendar (
    listing_id varchar(30),
    date_ date,
    available varchar(1),
    price float,
    adjusted_price float,
    minimum_nights int,
    maximum_nights int,
    PRIMARY KEY (listing_id, date_)
);
LOAD DATA LOCAL INFILE '/var/lib/mysql-files/calendar.csv'  
INTO TABLE calendar
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES 
(listing_id, date_, available, @price, @adjusted_price, minimum_nights, maximum_nights)
SET
    date_ = date_,
    available = available,
    price = REPLACE(REPLACE(@price, '$', ''), ',', '') + 0,  
    adjusted_price = REPLACE(REPLACE(@adjusted_price, '$', ''), ',', '') + 0,
    minimum_nights = minimum_nights, 
    maximum_nights = maximum_nights;  

CREATE TABLE listings_location (
    id varchar(30),
    name_ varchar(255),
    host_id varchar(50),
    neighbourhood_group varchar(50),
    neighbourhood varchar(50),
    latitude float,
    longitude float,
    PRIMARY KEY(id)
);
LOAD DATA LOCAL INFILE '/var/lib/mysql-files/listings.csv'
INTO TABLE listings_location
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id, name_, host_id, @dummy, neighbourhood_group, neighbourhood, latitude, longitude, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy);

CREATE TABLE listings_detail (
    id varchar(30),
    name_ varchar(50),
    property_type varchar(50),
    room_type varchar(50),
    accommodates int,
    bedrooms int,
    beds int,
    amenities varchar(2500),
    PRIMARY KEY (id)
);
LOAD DATA LOCAL INFILE '/var/lib/mysql-files/listings_detailed.csv'
INTO TABLE listings_detail
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id, @dummy, @dummy,@dummy,@dummy,name_,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,property_type,room_type,accommodates,@dummy,@dummy,bedrooms,beds,amenities,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy,@dummy);

CREATE TABLE review_detail (
    listing_id varchar(30),
    id varchar(30),
    date_ date,
    reviewer_id varchar(255),
    reviewer_name varchar(255),
    comments varchar(2500),
    PRIMARY KEY(listing_id, id)
);
LOAD DATA LOCAL INFILE '/var/lib/mysql-files/reviews_detailed.csv'
INTO TABLE review_detail
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(listing_id, id, date_, reviewer_id, reviewer_name, comments);

UPDATE review_detail
SET comments = REPLACE(REPLACE(comments, '</br>', ''), '<br/>', '');


CREATE TABLE host_detail (
    host_id varchar(50),
    host_name varchar(50),
    host_about varchar(7500),
    host_response_time varchar(50),
    host_response_rate float,
    host_is_superhost varchar(1),
    host_identity_verified varchar(1),
    PRIMARY KEY (host_id)
);
LOAD DATA LOCAL INFILE '/var/lib/mysql-files/listings_detailed.csv'
INTO TABLE host_detail
FIELDS TERMINATED BY ',' 
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, host_id, @dummy, host_name, @dummy, @dummy, host_about, host_response_time, host_response_rate, @dummy, host_is_superhost, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, host_identity_verified, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy);


CREATE TABLE listings_review_score (
    id varchar(30),
    number_of_reviews int,
    review_scores_rating float,
    review_scores_accuracy float,
    review_scores_cleanliness float,
    review_scores_checkin float,
    review_scores_communication float,
    review_scores_location float,
    review_scores_value float,
    PRIMARY KEY(id)
);

LOAD DATA LOCAL INFILE '/var/lib/mysql-files/listings_detailed.csv'
INTO TABLE listings_review_score
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, number_of_reviews, @dummy, @dummy, @dummy, @dummy, review_scores_rating, review_scores_accuracy, review_scores_cleanliness, review_scores_checkin, review_scores_communication, review_scores_location, review_scores_value, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy, @dummy);
