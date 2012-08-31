CREATE TABLE IF NOT EXISTS `user_data_resident` (
  `proxy_id` int(12) NOT NULL,
  `cmpa_no` int(11) NOT NULL,
  `cpso_no` int(11) NOT NULL,
  `school_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `student_no` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `assess_prog_img` varchar(1) NOT NULL,
  `assess_prog_non_img` varchar(1) NOT NULL,
  PRIMARY KEY (`proxy_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;