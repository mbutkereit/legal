-- 
-- Table structure for table legal_accepted
-- 

CREATE TABLE legal_accepted (
  legal_id serial,
  uid integer NOT NULL default '0',
  tc_id integer NOT NULL default '0',
  accepted integer NOT NULL default '0'
);

-- --------------------------------------------------------

-- 
-- Table structure for table legal_conditions
-- 

CREATE TABLE legal_conditions (
  tc_id serial,
  conditions longtext NOT NULL,
  date integer NOT NULL default '0'
);
