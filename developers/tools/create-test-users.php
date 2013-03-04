<?php
/**
 * Entrada Tools [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Generates some SQL to create random users for Entrada testing data.
 *
 * @author Unit: Medical Education Technology Unit
 * @author Developer: James Ellis <james.ellis@queensu.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
 */

date_default_timezone_set("America/Toronto");

$db_entrada = "entrada";
$db_auth = "entrada_auth";

$user_data		= array();
$user_access	= array();
$user_orgs		= array();
$group_members	= array();

$firstnames		= array("John", "William", "James", "Charles", "George", "Frank", "Joseph", "Thomas", "Henry", "Robert", "Edward", "Harry", "Walter", "Arthur", "Fred", "Albert", "Samuel", "David", "Louis", "Joe", "Charlie", "Clarence", "Richard", "Andrew", "Daniel", "Ernest", "Will", "Jesse", "Oscar", "Lewis", "Peter", "Benjamin", "Frederick", "Willie", "Alfred", "Sam", "Roy", "Herbert", "Jacob", "Tom", "Elmer", "Carl", "Lee", "Howard", "Martin", "Michael", "Bert", "Herman", "Jim", "Francis", "Harvey", "Earl", "Eugene", "Ralph", "Ed", "Claude", "Edwin", "Ben", "Charley", "Paul", "Edgar", "Isaac", "Otto", "Luther", "Lawrence", "Ira", "Patrick", "Guy", "Oliver", "Theodore", "Hugh", "Clyde", "Alexander", "August", "Floyd", "Homer", "Jack", "Leonard", "Horace", "Marion", "Philip", "Allen", "Archie", "Stephen", "Chester", "Willis", "Raymond", "Rufus", "Warren", "Jessie", "Milton", "Alex", "Leo", "Julius", "Ray", "Sidney", "Bernard", "Dan", "Jerry", "Calvin", "Perry", "Dave", "Anthony", "Eddie", "Amos", "Dennis", "Clifford", "Leroy", "Wesley", "Alonzo", "Garfield", "Franklin", "Emil", "Leon", "Nathan", "Harold", "Matthew", "Levi", "Moses", "Everett", "Lester", "Winfield", "Adam", "Lloyd", "Mack", "Fredrick", "Jay", "Jess", "Melvin", "Noah", "Aaron", "Alvin", "Norman", "Gilbert", "Elijah", "Victor", "Gus", "Nelson", "Jasper", "Silas", "Christopher", "Jake", "Mike", "Percy", "Adolph", "Maurice", "Cornelius", "Felix", "Reuben", "Wallace", "Claud", "Roscoe", "Sylvester", "Earnest", "Hiram", "Otis", "Simon", "Willard", "Irvin", "Mark", "Jose", "Wilbur", "Abraham", "Virgil", "Clinton", "Elbert", "Leslie", "Marshall", "Owen", "Wiley", "Anton", "Morris", "Manuel", "Phillip", "Augustus", "Emmett", "Eli", "Nicholas", "Wilson", "Alva", "Harley", "Newton", "Timothy", "Marvin", "Ross", "Curtis", "Edmund", "Jeff", "Elias", "Harrison", "Stanley", "Columbus", "Lon", "Ora", "Ollie", "Russell", "Pearl", "Solomon", "Arch", "Asa", "Clayton", "Enoch", "Irving", "Mathew", "Nathaniel", "Scott", "Hubert", "Lemuel", "Andy", "Ellis", "Emanuel", "Joshua", "Millard", "Vernon", "Wade", "Cyrus", "Miles", "Rudolph", "Sherman", "Austin", "Bill", "Chas", "Lonnie", "Monroe", "Byron", "Edd", "Emery", "Grant", "Jerome", "Max", "Mose", "Steve", "Gordon", "Abe", "Pete", "Chris", "Clark", "Gustave", "Orville", "Lorenzo", "Bruce", "Marcus", "Preston", "Bob", "Dock", "Donald", "Jackson", "Cecil", "Barney", "Delbert", "Edmond", "Anderson", "Christian", "Glenn", "Jefferson", "Luke", "Neal", "Burt", "Ike", "Myron", "Tony", "Conrad", "Joel", "Matt", "Riley", "Vincent", "Emory", "Isaiah", "Nick", "Ezra", "Green", "Juan", "Clifton", "Lucius", "Porter", "Arnold", "Bud", "Jeremiah", "Taylor", "Forrest", "Roland", "Spencer", "Burton", "Don", "Emmet", "Gustav", "Louie", "Morgan", "Ned", "Van", "Ambrose", "Chauncey", "Elisha", "Ferdinand", "General", "Julian", "Kenneth", "Mitchell", "Allie", "Josh", "Judson", "Lyman", "Napoleon", "Pedro", "Berry", "Dewitt", "Ervin", "Forest", "Lynn", "Pink", "Ruben", "Sanford", "Ward", "Douglas", "Ole", "Omer", "Ulysses", "Walker", "Wilbert", "Adelbert", "Benjiman", "Ivan", "Jonas", "Major", "Abner", "Archibald", "Caleb", "Clint", "Dudley", "Granville", "King", "Mary", "Merton", "Antonio", "Bennie", "Carroll", "Freeman", "Josiah", "Milo", "Royal", "Dick", "Earle", "Elza", "Emerson", "Fletcher", "Judge", "Laurence", "Neil", "Roger", "Seth", "Glen", "Hugo", "Jimmie", "Johnnie", "Washington", "Elwood", "Gust", "Harmon", "Jordan", "Simeon", "Wayne", "Wilber", "Clem", "Evan", "Frederic", "Irwin", "Junius", "Lafayette", "Loren", "Madison", "Mason", "Orval", "Abram", "Aubrey", "Elliott", "Hans", "Karl", "Minor", "Wash", "Wilfred", "Allan", "Alphonse", "Dallas", "Dee", "Isiah", "Jason", "Johnny", "Lawson", "Lew", "Micheal", "Orin", "Addison", "Cal", "Erastus", "Francisco", "Hardy", "Lucien", "Randolph", "Stewart", "Vern", "Wilmer", "Zack", "Adrian", "Alvah", "Bertram", "Clay", "Ephraim", "Fritz", "Giles", "Grover", "Harris", "Isom", "Jesus", "Johnie", "Jonathan", "Lucian", "Malcolm", "Merritt", "Otho", "Perley", "Rolla", "Sandy", "Tomas", "Wilford", "Adolphus", "Angus", "Arther", "Carlos", "Cary", "Cassius", "Davis", "Hamilton", "Harve", "Israel", "Leander", "Melville", "Merle", "Murray", "Pleasant", "Sterling", "Steven", "Axel", "Boyd", "Bryant", "Clement", "Erwin", "Ezekiel", "Foster", "Frances", "Geo", "Houston", "Issac", "Jules", "Larkin", "Mat", "Morton", "Orlando", "Pierce", "Prince", "Rollie", "Rollin", "Sim", "Stuart", "Wilburn", "Bennett", "Casper", "Christ", "Dell", "Egbert", "Elmo", "Fay", "Gabriel", "Hector", "Horatio", "Lige", "Saul", "Smith", "Squire", "Tobe", "Tommie", "Wyatt", "Alford", "Alma", "Alton", "Andres", "Burl", "Cicero", "Dean", "Dorsey", "Enos", "Howell", "Lou", "Loyd", "Mahlon", "Nat", "Omar", "Oran", "Parker", "Raleigh", "Reginald");
$lastnames		= array("smith", "johnson", "williams", "brown", "jones", "miller", "davis", "garcia", "rodriguez", "wilson", "martinez", "anderson", "taylor", "thomas", "hernandez", "moore", "martin", "jackson", "thompson", "white", "lopez", "lee", "gonzalez", "harris", "clark", "lewis", "robinson", "walker", "perez", "hall", "young", "allen", "sanchez", "wright", "king", "scott", "green", "baker", "adams", "nelson", "hill", "ramirez", "campbell", "mitchell", "roberts", "carter", "phillips", "evans", "turner", "torres", "parker", "collins", "edwards", "stewart", "flores", "morris", "nguyen", "murphy", "rivera", "cook", "rogers", "morgan", "peterson", "cooper", "reed", "bailey", "bell", "gomez", "kelly", "howard", "ward", "cox", "diaz", "richardson", "wood", "watson", "brooks", "bennett", "gray", "james", "reyes", "cruz", "hughes", "price", "myers", "long", "foster", "sanders", "ross", "morales", "powell", "sullivan", "russell", "ortiz", "jenkins", "gutierrez", "perry", "butler", "barnes", "fisher", "henderson", "coleman", "simmons", "patterson", "jordan", "reynolds", "hamilton", "graham", "kim", "gonzales", "alexander", "ramos", "wallace", "griffin", "west", "cole", "hayes", "chavez", "gibson", "bryant", "ellis", "stevens", "murray", "ford", "marshall", "owens", "mcdonald", "harrison", "ruiz", "kennedy", "wells", "alvarez", "woods", "mendoza", "castillo", "olson", "webb", "washington", "tucker", "freeman", "burns", "henry", "vasquez", "snyder", "simpson", "crawford", "jimenez", "porter", "mason", "shaw", "gordon", "wagner", "hunter", "romero", "hicks", "dixon", "hunt", "palmer", "robertson", "black", "holmes", "stone", "meyer", "boyd", "mills", "warren", "fox", "rose", "rice", "moreno", "schmidt", "patel", "ferguson", "nichols", "herrera", "medina", "ryan", "fernandez", "weaver", "daniels", "stephens", "gardner", "payne", "kelley", "dunn", "pierce", "arnold", "tran", "spencer", "peters", "hawkins", "grant", "hansen", "castro", "hoffman", "hart", "elliott", "cunningham", "knight", "bradley", "carroll", "hudson", "duncan", "armstrong", "berry", "andrews", "johnston", "ray", "lane", "riley", "carpenter", "perkins", "aguilar", "silva", "richards", "willis", "matthews", "chapman", "lawrence", "garza", "vargas", "watkins", "wheeler", "larson", "carlson", "harper", "george", "greene", "burke", "guzman", "morrison", "munoz", "jacobs", "obrien", "lawson", "franklin", "lynch", "bishop", "carr", "salazar", "austin", "mendez", "gilbert", "jensen", "williamson", "montgomery", "harvey", "oliver", "howell", "dean", "hanson", "weber", "garrett", "sims", "burton", "fuller", "soto", "mccoy", "welch", "chen", "schultz", "walters", "reid", "fields", "walsh", "little", "fowler", "bowman", "davidson", "may", "day", "schneider", "newman", "brewer", "lucas", "holland", "wong", "banks", "santos", "curtis", "pearson", "delgado", "valdez", "pena", "rios", "douglas", "sandoval", "barrett", "hopkins", "keller", "guerrero", "stanley", "bates", "alvarado", "beck", "ortega", "wade", "estrada", "contreras", "barnett", "caldwell", "santiago", "lambert", "powers", "chambers", "nunez", "craig", "leonard", "lowe", "rhodes", "byrd", "gregory", "shelton", "frazier", "becker", "maldonado", "fleming", "vega", "sutton", "cohen", "jennings", "parks", "mcdaniel", "watts", "barker", "norris", "vaughn", "vazquez", "holt", "schwartz", "steele", "benson", "neal", "dominguez", "horton", "terry", "wolfe", "hale", "lyons", "graves", "haynes", "miles", "park", "warner", "padilla", "bush", "thornton", "mccarthy", "mann", "zimmerman", "erickson", "fletcher", "mckinney", "page", "dawson", "joseph", "marquez", "reeves", "klein", "espinoza", "baldwin", "moran", "love", "robbins", "higgins", "ball", "cortez", "le", "griffith", "bowen", "sharp", "cummings", "ramsey", "hardy", "swanson", "barber", "acosta", "luna", "chandler", "blair", "daniel", "cross", "simon", "dennis", "oconnor", "quinn", "gross", "navarro", "moss", "fitzgerald", "doyle", "mclaughlin", "rojas", "rodgers", "stevenson", "singh", "yang", "figueroa", "harmon", "newton", "paul", "manning", "garner", "mcgee", "reese", "francis", "burgess", "adkins", "goodman", "curry", "brady", "christensen", "potter", "walton", "goodwin", "mullins", "molina", "webster", "fischer", "campos", "avila", "sherman", "todd", "chang", "blake", "malone", "wolf", "hodges", "juarez", "gill", "farmer", "hines", "gallagher", "duran", "hubbard", "cannon", "miranda", "wang", "saunders", "tate", "mack", "hammond", "carrillo", "townsend", "wise", "ingram", "barton", "mejia", "ayala", "schroeder", "hampton", "rowe", "parsons", "frank", "waters", "strickland", "osborne", "maxwell", "chan", "deleon", "norman", "harrington", "casey", "patton", "logan", "bowers", "mueller", "glover", "floyd", "hartman", "buchanan", "cobb", "french", "kramer", "mccormick", "clarke", "tyler", "gibbs", "moody", "conner", "sparks", "mcguire", "leon", "bauer", "norton", "pope", "flynn", "hogan", "robles", "salinas", "yates", "lindsey", "lloyd", "marsh", "mcbride", "owen", "solis", "pham", "lang", "pratt");

$group			= "student";
$role			= "2014";

$cohorts = array(
	"2012" => 1,
	"2013" => 2,
	"2014" => 3
);

foreach	(range(1, 100) as $proxy_id) {
	$user_data[]	= "(".$proxy_id.", 0, '".$group.$proxy_id."', MD5('password'), 1, NULL, '', '".ucwords(strtolower($firstnames[array_rand($firstnames)]))."', '".ucwords(strtolower($lastnames[array_rand($lastnames)]))."', '".$group.$proxy_id."@demo.entrada-project.org', '', NULL, NULL, '', '', '', 'Edmonton', '', '', '', 39, 1, '', '', 0, 0, NULL, NULL, 0, 1, 0, 0)";

	$user_access[]	= "(NULL, ".$proxy_id.", 1, 'true', ".time().", 0, 0, '', NULL, NULL, '".$role."', '".$group."', '', MD5(CONCAT(rand(), CURRENT_TIMESTAMP, ".$proxy_id.")), '')";

	$user_orgs[]	= "(NULL, 1, ".$proxy_id.")";

	if ($group == "student" && array_key_exists($role, $cohorts)) {
		$group_members[] = "(NULL, ".$cohorts[$role].", ".$proxy_id.", ".strtotime("September 1 ".($role - 4). "00:00:00").", ".strtotime("May 31 ".$role. "23:59:59").", 1, 0, 0, 0)";
	}

}

echo "INSERT INTO `".$db_auth."`.`user_data` (`id`, `number`, `username`, `password`, `organisation_id`, `department`, `prefix`, `firstname`, `lastname`, `email`, `email_alt`, `email_updated`, `google_id`, `telephone`, `fax`, `address`, `city`, `province`, `postcode`, `country`, `country_id`, `province_id`, `notes`, `office_hours`, `privacy_level`, `notifications`, `entry_year`, `grad_year`, `gender`, `clinical`, `updated_date`, `updated_by`) VALUES\n";
echo implode(",\n", $user_data).";\n\n";

echo "INSERT INTO `".$db_auth."`.`user_access` (`id`, `user_id`, `app_id`, `account_active`, `access_starts`, `access_expires`, `last_login`, `last_ip`, `login_attempts`, `locked_out_until`, `role`, `group`, `extras`, `private_hash`, `notes`) VALUES\n";
echo implode(",\n", $user_access).";\n\n";

echo "INSERT INTO `user_organisations` (`id`, `organisation_id`, `proxy_id`) VALUES\n";
echo implode(",\n", $user_orgs).";\n\n";

if ($group == "student" && array_key_exists($role, $cohorts)) {
	echo "INSERT INTO `".$db_entrada."`.`group_members` (`gmember_id`, `group_id`, `proxy_id`, `start_date`, `finish_date`, `member_active`, `entrada_only`, `updated_date`, `updated_by`) VALUES\n";
	echo implode(",\n", $group_members).";\n\n";
}

?>