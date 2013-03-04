CREATE TABLE IF NOT EXISTS `medbiq_assessment_methods` (
  `assessment_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `assessment_method` varchar(250) NOT NULL DEFAULT '',
  `assessment_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`assessment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_assessment_methods` (`assessment_method_id`, `assessment_method`, `assessment_method_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Clinical Documentation Review','The review and assessment of clinical notes and logs kept by learners as part of practical training in the clinical setting (Bowen & Smith, 2010; Irby, 1995)',1,0,0),
(2,'Clinical Performance Rating/Checklist','A non-narrative assessment tool (checklist, Likert-type scale, other instrument) used to note completion or\rachievement of learning tasks (MacRae, Vu, Graham, Word-Sims, Colliver, & Robbs, 1995; Turnbull, Gray, & MacFadyen, 1998) also see ?Direct Observations or Performance Audits,? Institute for International Medical Education, 2002)',1,0,0),
(3,'Exam - Institutionally Developed, Clinical Performance','Practical performance-based examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011) (Includes observation of learner or small group by instructor)',1,0,0),
(4,'Exam - Institutionally Developed, Written/Computer-based','Examination utilizing various written question-and-answer formats (multiple-choice, short answer, essay, etc.) which may assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning (Cooke, Irby, & O?Brien, 2010b; LCME, 2011)',1,0,0),
(5,'Exam - Institutionally Developed, Oral','Verbal examination developed internally to assess problem solving, clinical reasoning, decision making, and[/or] communication skills (LCME, 2011)',1,0,0),
(6,'Exam - Licensure, Clinical Performance','Practical, performance-based examination developed by a professional licensing body to assess clinical skills such as problem solving, clinical reasoning, decision making, and communication, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a written/computer-based component (MCC, 2011a & 2011c; NBOME, 2010b; USMLE, n.d.); may also be used by schools to assess learners? achievement of certain curricular objectives',1,0,0),
(7,'Exam - Licensure, Written/Computer-based','Standardized written examination administered to assess learners\' factual knowledge retention; application of knowledge, concepts, and principles; problem-solving acumen; and clinical reasoning, for licensure to practice in a given jurisdiction (e.g., USMLE for the United States); typically paired with a clinical performance component (MCC, 2011a & 2011b; NBOME, 2010b; USMLE, n.d.); may also be used by schools or learners themselves to assess achievement of certain curricular objectives',1,0,0),
(8,'Exam - Nationally Normed/Standardized, Subject','Standardized written examination administered to assess learners? achievement of nationally established educational expectations for various levels of training and/or specialized subject area(s) (e.g., NBME Subject or ?Shelf? Exam) (NBME, 2011; NBOME, 2010a)',1,0,0),
(9,'Multisource Assessment','A formal assessment of performance by supervisors, peers, patients, and coworkers (Bowen & Smith, 2010; Institute for International Medical Education, 2002) (Also see Peer Assessment)',1,0,0),
(10,'Narrative Assessment','An instructor\'s or observer\'s written subjective assessment of a learner\'s work or performance (Mennin, McConnell, & Anderson, 1997); May Include: Comments within larger assessment; Observation of learner or small group by instructor',1,0,0),
(11,'Oral Patient Presentation','The presentation of clinical case (patient) findings, history and physical, differential diagnosis, treatment plan, etc., by a learner to an instructor or small group, and subsequent discussion with the instructor and/or small group for the purposes of learner demonstrating skills in clinical reasoning, problem-solving, etc.\r(Wiener, 1974)',1,0,0),
(12,'Participation','Sharing or taking part in an activity (Education Resources Information Center, 1966b)',1,0,0),
(13,'Peer Assessment','The concurrent or retrospective review by learners of the quality and efficiency of practices or services ordered or performed by fellow learners (based on MeSH Scope Note for \"Peer Review, Health Care,\" U.S. National Library of Medicine, 1992)',1,0,0),
(14,'Portfolio-Based Assessment','Review of a learner\'s achievement of agreed-upon academic objectives or completion of a negotiated set of learning activities, based on a learner portfolio (Institute for International Medical Education, 2002) (\"a systematic collection of a student\'s work samples, records of observation, test results, etc., over a period of time\"? Education Resources Information Center, 1994)',1,0,0),
(15,'Practical (Lab)','Learner engagement in hands-on or simulated exercises in which they collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),
(16,'Research or Project Assessment','Assessment of activities and outcomes (e.g., posters, presentations, reports, etc.) of a project in which the learner participated or conducted research (Dyrbye, Davidson, & Cook, 2008)',1,0,0),
(17,'Self-Assessment','The process of evaluating one?s own deficiencies, achievements, behavior or professional performance and competencies (Institute for International Medical Education, 2002); Assessment completed by the learner to reflect and critically assess his/her own performance against a set of established criteria (Gordon, 1991) (NOTE: Does not refer to NBME Self-Assessment)',1,0,0),
(18,'Stimulated Recall','The use of various stimuli (e.g., written records, audio tapes, video tapes) to re-activate the experience of a learner during a learning activity or clinical encounter in order to reflect on task performance, reasoning, decision-making, interpersonal skills, personal thoughts and feelings, etc. (Barrows, 2000)',1,0,0);

CREATE TABLE IF NOT EXISTS `medbiq_instructional_methods` (
  `instructional_method_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `instructional_method` varchar(250) NOT NULL DEFAULT '',
  `instructional_method_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`instructional_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_instructional_methods` (`instructional_method_id`, `instructional_method`, `instructional_method_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Case-Based Instruction/Learning','The use of patient cases (actual or theoretical) to stimulate discussion, questioning, problem solving, and reasoning on issues pertaining to the basic sciences and clinical disciplines (Anderson, 2010)',1,0,0),
(2,'Clinical Experience - Ambulatory','Practical experience(s) in patient care and health-related services carried out in an ambulatory/outpatient\rsetting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),
(3,'Clinical Experience - Inpatient','Practical experience(s) in patient care and health-related services carried out in an inpatient setting (LCME, 2011) where actual symptoms are studied and treatment is given (Education Resources Information Center, 1968 & 1981)',1,0,0),
(4,'Concept Mapping','Technique [that] allows learners to organize and represent knowledge in an explicit interconnected network. Linkages between concepts are explored to make apparent connections that are not usually seen. Concept mapping also encourages the asking of questions about relationships between concepts that may not have been presented in traditional courses, standard texts, and teaching materials. It shifts the focus of learning away from rote acquisition of information to visualizing the underlying concepts that provide the cognitive\rframework of what the learner already knows, to facilitate the acquisition of new knowledge (Weiss & Levinson, 2000, citing Novak & Gowin, 1984)',1,0,0),
(5,'Conference','Departmentally-driven and/or content-specific presentations by clinical faculty/professionals, residents,\rand/or learners before a large group of other professionals and/or learners (e.g., Mortality and Morbidity, or \"M & M,\" Conference--Biddle & Oaster, 1990--and Interdisciplinary Conference--Feldman, 1999; also see Cooke, Irby, & O\'Brien, 2010b)',1,0,0),
(6,'Demonstration','A description, performance, or explanation of a process, illustrated by examples, observable action, specimens, etc. (Dictionary.com, n.d.)',1,0,0),
(7,'Discussion, Large Group (>13)','An exchange (oral or written) of opinions, observations, or ideas among a Large Group [more than 12\rparticipants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),
(8,'Discussion, Small Group (&lt;12)','An exchange (oral or written) of opinions, observations, or ideas among a Small Group [12 or fewer participants], usually to analyze, clarify, or reach conclusions about issues, questions, or problems (Education Resources Information Center, 1980)',1,0,0),
(9,'Games','Individual or group games that have cognitive, social, behavioral, and/or emotional, etc., dimensions which are related to educational objectives (Education Resources Information Center, 1966a)',1,0,0),
(10,'Independent Learning','Instructor-/ or mentor-guided learning activities to be performed by the learner outside of formal educational settings (classroom, lab, clinic) (Bowen & Smith, 2010); Dedicated time on learner schedules to prepare for specific learning activities, e.g., case discussions, TBL, PBL, clinical activities, research project(s)',1,0,0),
(11,'Journal Club','A forum in which participants discuss recent research papers from field literature in order to develop\rcritical reading skills (comprehension, analysis, and critique) (Cooke, Irby, & O\'Brien, 2010a; Mann & O\'Neill, 2010; Woods & Winkel, 1982)',1,0,0),
(12,'Laboratory','Hands-on or simulated exercises in which learners collect or use data to test and/or verify hypotheses or to address questions about principles and/or phenomena (LCME, 2011)',1,0,0),
(13,'Lecture','An instruction or verbal discourse by a speaker before a large group of learners (Institute for International Medical Education, 2002)',1,0,0),
(14,'Mentorship','The provision of guidance, direction and support by senior professionals to learners or more junior professionals (U.S. National Library of Medicine, 1987)',1,0,0),
(15,'Patient Presentation - Faculty','A presentation by faculty of patient findings, history and physical, differential diagnosis, treatment plan,\retc. (Wiener, 1974)',1,0,0),
(16,'Patient Presentation - Learner','A presentation by a learner or learners to faculty, resident(s), and/or other learners of patient findings, history and physical, differential diagnosis, treatment plan, etc. (Wiener, 1974)',1,0,0),
(17,'Peer Teaching','Learner-to-learner instruction for the mutual learning experience of both \"teacher\" and \"learner\"; may be \"peer-to-peer\" (same training level) or \"near-peer\" (higher-level learner teaching lower-level learner)\r(Soriano et al., 2010)',1,0,0),
(18,'Preceptorship','Practical experience in medical and health-related services wherein the professionally-trained learner works\runder the supervision of an established professional in the particular field (U. S. National Library of Medicine, 1974)',1,0,0),
(19,'Problem-Based Learning (PBL)','The use of carefully selected and designed patient cases that demand from the learner acquisition of critical\rknowledge, problem solving proficiency, self-directed learning strategies, and team participation skills as those needed in professional practice (Eshach & Bitterman, 2003; see also Major & Palmer, 2001; Cooke, Irby, & O\'Brien, 2010b;\rBarrows & Tamblyn, 1980)',1,0,0),
(20,'Reflection','Examination by the learner of his/her personal experiences of a learning event, including the cognitive, emotional, and affective aspects; the use of these past experiences in combination with objective information\rto inform present clinical decision-making and problem-solving (Mann, Gordon, & MacLeod, 2009; Mann & O\'Neill, 2010)',1,0,0),
(21,'Research','Short-term or sustained participation in research',1,0,0),
(22,'Role Play/Dramatization','The adopting or performing the role or activities of another individual',1,0,0),
(23,'Self-Directed Learning','Learners taking the initiative for their own learning: diagnosing needs, formulating goals, identifying resources, implementing appropriate activities, and evaluating outcomes (Garrison, 1997; Spencer & Jordan, 1999)',1,0,0),
(24,'Service Learning Activity','A structured learning experience that combines community service with preparation and reflection (LCME, 2011)',1,0,0),
(25,'Simulation','A method used to replace or amplify real patient encounters with scenarios designed to replicate real health care situations, using lifelike mannequins, physical models, standardized patients, or computers (Passiment,\rSacks, & Huang, 2011)',1,0,0),
(26,'Team-Based Learning (TBL)','A form of collaborative learning that follows a specific sequence of individual work, group work and immediate feedback; engages learners in learning activities within a small group that works independently in classes with high learner-faculty ratios (Anderson, 2010; Team-Based Learning Collaborative, n.d.; Thompson, Schneider, Haidet, Perkowski, & Richards, 2007)',1,0,0),
(27,'Team-Building','Workshops, sessions, and/or activities contributing to the development of teamwork skills, often as a foundation for group work in learning (PBL, TBL, etc.) and practice (interprofessional/-disciplinary, etc.)\r(Morrison, Goldfarb, & Lanken, 2010)',1,0,0),
(28,'Tutorial','Instruction provided to a learner or small group of learners by direct interaction with an instructor (Education\rResources Information Center, 1966c)',1,0,0),
(29,'Ward Rounds','An instructional session conducted in an actual clinical setting, using real patients or patient cases to demonstrate procedures or clinical skills, illustrate clinical reasoning and problem-solving, or stimulate discussion and analytical thinking among a group of learners (Bowen & Smith, 2010; Wiener, 1974)',1,0,0),
(30,'Workshop','A brief intensive educational program for a relatively small group of people that focuses especially on techniques and skills related to a specific topic (U. S. National Library of Medicine, 2011)',1,0,0);

CREATE TABLE IF NOT EXISTS `medbiq_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(250) NOT NULL DEFAULT '',
  `resource_description` text,
  `active` int(1) NOT NULL DEFAULT '1',
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `medbiq_resources` (`resource_id`, `resource`, `resource_description`, `active`, `updated_date`, `updated_by`) VALUES
(1,'Audience Response System','An electronic communication system that allows groups of people to vote on a topic or answer a question. Each person has a remote control (\"clicker\") with which selections can be made; Typically, the results are\rinstantly made available to the participants via a graph displayed on the projector. (Group on Information Resources, 2011; Stoddard & Piquette, 2010)',1,0,0),
(2,'Audio','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using auditory delivery (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),
(3,'Cadaver','A human body preserved post-mortem and \"used...to study anatomy, identify disease sites, determine causes of death, and provide tissue to repair a defect in a living human being\" (MedicineNet.com, 2004)',1,0,0),
(4,'Clinical Correlation','The application and elaboration of concepts introduced in lecture, reading assignments, independent study, and other learning activities to real patient or case scenarios in order to promote knowledge retrieval in similar clinical situations at a later time (Euliano, 2001)',1,0,0),
(5,'Distance Learning - Asynchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, and which \"does not occur in real time or involve simultaneous interaction on the part of participants. It is intermittent and generally characterized by a significant time delay or interval between sending and receiving or responding to messages\" (Education Resources Information Center, 1983; 2008a)',1,0,0),
(6,'Distance Learning - Synchronous','Education facilitated through communications media (often electronic), with little or no classroom or other face-to-face contact between learners and teachers, \"in real time, characterized by concurrent exchanges between participants. Interaction is simultaneous without a meaningful time delay between sending a message and receiving or responding to it. Occurs in electronic (e.g., interactive videoconferencing) and non-electronic environments (e.g., telephone conversations)\" (Education Resources Information Center, 1983; 2008c)',1,0,0),
(7,'Educational Technology','Mobile or desktop technology (hardware or software) used for instruction/learning through audiovisual (A/V), multimedia, web-based, or online modalities (Group on Information Resources, 2011); Sometimes includes dedicated space (see Virtual/Computerized Lab)',1,0,0),
(8,'Electronic Health/Medical Record (EHR/EMR)','An individual patient\'s medical record in digital format...usually accessed on a computer, often over a network...[M]ay be made up of electronic medical records (EMRs) from many locations and/or sources. An Electronic Medical Record (EMR) may be an inpatient or outpatient medical record in digital format that may or may not be linked to or part of a larger EHR (Group on Information Resources, 2011)',1,0,0),
(9,'Film/Video','Devices or applications used to acquire or transfer knowledge, attitudes, or skills through study, instruction, or experience using visual recordings (see \"Electronic Learning,\" Education Resources Information Center, 2008b)',1,0,0),
(10,'Key Feature','An element specific to a clinical case or problem that demands the use of particular clinical skills in order to achieve the problem\'s successful resolution; Typically presented as written exam questions, as in the Canadian Qualifying Examination in Medicine (Page & Bordage, 1995; Page, Bordage, & Allen, 1995)',1,0,0),
(11,'Mannequin','A life-size model of the human body that mimics various anatomical functions to teach skills and procedures in health education; may be low-fidelity (having limited or no electronic inputs) or high-fidelity\r(connected to a computer that allows the robot to respond dynamically to user input) (Group on Information Resources, 2011; Passiment, Sacks, & Huang, 2011)',1,0,0),
(12,'Plastinated Specimens','Organic material preserved by replacing water and fat in tissue with silicone, resulting in \"anatomical specimens [that] are safer to use, more pleasant to use, and are much more durable and have a much longer shelf life\" (University of Michigan Plastination Lab, n.d.); See also: Wet Lab',1,0,0),
(13,'Printed Materials (or Digital Equivalent)','Reference materials produced or selected by faculty to augment course teaching and learning',1,0,0),
(14,'Real Patient','An actual clinical patient',1,0,0),
(15,'Searchable Electronic Database','A collection of information organized in such a way that a computer program can quickly select desired pieces of data (Webopedia, n.d.)',1,0,0),
(16,'Standardized/Simulated Patient (SP)','Individual trained to portray a patient with a specific condition in a realistic, standardized and repeatable way (where portrayal/presentation varies based only on learner performance) (ASPE, 2011)',1,0,0),
(17,'Task Trainer','A physical model that simulates a subset of physiologic function to include normal and abnormal anatomy (Passiment, Sacks, & Huang, 2011); Such models which provide just the key elements of the task or skill being learned (CISL, 2011)',1,0,0),
(18,'Virtual Patient','An interactive computer simulation of real-life clinical scenarios for the purpose of medical training, education, or assessment (Smothers, Azan, & Ellaway, 2010)',1,0,0),
(19,'Virtual/Computerized Laboratory','A practical learning environment in which technology- and computer-based simulations allow learners to engage in computer-assisted instruction while being able to ask and answer questions and also engage in discussion of content (Cooke, Irby, & O\'Brien, 2010a); also, to learn through experience by performing medical tasks, especially high-risk ones, in a safe environment (Uniformed Services University, 2011)',1,0,0),
(20,'Wet Laboratory','Facilities outfitted with specialized equipment* and bench space or adjustable, flexible desktop space for working with solutions or biological materials (\"C.1 Wet Laboratories,\" 2006; Stanford University School of Medicine, 2007;\rWBDG Staff, 2010) *Often includes sinks, chemical fume hoods, biosafety cabinets, and piped services such as deionized or RO water, lab cold and hot water, lab waste/vents, carbon dioxide, vacuum, compressed air, eyewash, safety showers, natural gas, telephone, LAN, and power (\"C.1 Wet Laboratories,\" 2006)',1,0,0);

CREATE TABLE IF NOT EXISTS `map_assessments_meta` (
  `map_assessments_meta_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_assessment_method_id` int(11) NOT NULL,
  `fk_assessments_meta_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_assessments_meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `map_event_resources` (
  `map_event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_medbiq_resource_id` int(11) DEFAULT NULL,
  `fk_resource_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `map_events_eventtypes` (
  `map_events_eventtypes_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_instructional_method_id` int(11) NOT NULL,
  `fk_eventtype_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`map_events_eventtypes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `event_resources` (
  `event_resources_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fk_resource_id` int(11) NOT NULL,
  `fk_event_id` int(11) NOT NULL,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`event_resources_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `events_lu_resources` (
  `resource_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(250) NOT NULL DEFAULT '',
  `description` text,
  `updated_date` bigint(64) NOT NULL,
  `updated_by` int(11) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE `settings` SET `value` = '1303' WHERE `shortname` = 'version_db';