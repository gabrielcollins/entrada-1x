<?php
echo "<div style=\"padding: 2px; padding-left: 5px; border-bottom: 1px #9D9D9D dotted\">\n";
echo "<img src=\"".ENTRADA_URL."/images/arrow-right.gif\" width=\"5\" height=\"9\" alt=\"\" align=\"absmiddle\" /> <a href=\"".ENTRADA_URL."\">".APPLICATION_NAME."</a> \n";
echo "<img src=\"".ENTRADA_URL."/images/pixel.gif\" width=\"5\" height=\"9\" alt=\"\" valign=\"absmiddle\" /><img src=\"".ENTRADA_URL."/images/arrow-right.gif\" width=\"5\" height=\"9\" alt=\"\" valign=\"absmiddle\" /> Privacy Policy\n";
echo "</div><br />\n";

// Meta information for this page.
$PAGE_META["title"]			= "Privacy Policy";
$PAGE_META["description"]	= "This page contains the human readable privacy policy for our online learning environment.";
$PAGE_META["keywords"]		= "privacy, policy, online, course, resources, application";

?>

<h1>Privacy Policy</h1>

<!-- "About Us" section of privacy policy -->
<h2>About Us</h2>
This is a privacy policy for <?php echo APPLICATION_NAME; ?>, which is located on the Internet at <a href="<?php echo ENTRADA_URL; ?>"><?php echo ENTRADA_URL; ?></a>.
<br /><br />
We invite you to contact us if you have questions about this policy. You may contact us by e-mail at <a href="mailto:<?php echo $AGENT_CONTACTS["administrator"]["email"]; ?>"><?php echo $AGENT_CONTACTS["administrator"]["email"]; ?></a>.
<br /><br />
<!-- "Privacy Seals" section of privacy policy -->
<h2>Dispute Resolution and Privacy Seals</h2>
We have the following privacy seals and/or dispute resolution mechanisms. If you think we have not followed our privacy policy in some way, they can help you resolve your concern.
<ul>
	<li>
		<strong><?php echo APPLICATION_NAME; ?></strong>:<br />
		You can contact us to resolve privacy policy issues: <a href="mailto:<?php echo $AGENT_CONTACTS["administrator"]["email"]; ?>"><?php echo $AGENT_CONTACTS["administrator"]["email"]; ?></a>
	</li>
</ul>

<!-- "Additional information" section of privacy policy -->
<h2>Additional Information</h2>
This policy is valid for 60 days from the time that it is loaded by a client.
<br /><br />

<!-- "Data Collection" section of privacy policy -->
<h2>Data Collection</h2>
P3P policies declare the data they collect in groups (also referred to as &quot;statements&quot;). This policy contains 2 data groups. The data practices of each group will be explained separately.
<blockquote>
	<strong>Group &quot;Basic information&quot;</strong><br />
	We collect the following information:
	<ul>
		<li>Click-stream data</li>
		<li>HTTP protocol elements</li>
		<li>Search terms</li>
	</ul>
	
	This data will be used for the following purposes:
	<ul>
		<li>Completion and support of the current activity.</li>
		<li>Web site and system administration.</li>
		<li>Research and development.</li>
	</ul>
	
	This data will be used by ourselves and our agents.
	<br /><br />
	
	The following explanation is provided for why this data is collected:
	<blockquote>
		Data collected from all Web users: access logs, and search strings (if entered).
	</blockquote>
	
	<strong>Group &quot;Cookies&quot;</strong><br />
	At the user's option, we will collect the following data:
	<ul>
		<li>HTTP cookies</li>
	</ul>
	This data will be used for the following purposes:
	<ul>
		<li>Research and development.</li>
		<li>One-time tailoring.</li>
	</ul>
	
	This data will be used by ourselves and our agents.
	<br /><br />
	
	The following explanation is provided for why this data is collected:
	<blockquote>
		Cookies are used to track visitors to our site, so we can better understand what portions of our site best serve you.
	</blockquote>
</blockquote>

<!-- "Use of Cookies" section of privacy policy -->
<h2>Cookies</h2>
Cookies are a technology which can be used to provide you with tailored information from a Web site. A cookie is an element of data that a Web site can send to your browser, which may then store it on your system. You can set your browser to notify you when you receive a cookie, giving you the chance to decide whether to accept it.
<br /><br />
Our site makes use of cookies. Cookies are used for the following purposes:
<ul>
	<li>User targeting
	<li>Research and development
</ul>

<!-- "Compact Policy Explanation" section of privacy policy -->
<h2>Compact Policy Summary</h2>
The compact policy which corresponds to this policy is:
<pre>
    CP="NON DSP COR CURa ADMa DEVa TAIa OUR BUS IND UNI COM NAV INT"
</pre>

The following table explains the meaning of each field in the compact policy.
<div align="center">
<table width="80%" border="0" cellspacing="0" cellpadding="2">
<tr>
	<td align="left" valign="top" width="15%" style="border-bottom: 1px #CCCCCC dotted"><strong>Field</strong></td>
	<td align="left" valign="top" width="85%" style="border-bottom: 1px #CCCCCC dotted"><strong>Meaning</strong></td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>CP=</tt></td>
	<td align="left" valign="top" width="85%">This is the compact policy header; it indicates that what follows is a P3P compact policy.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>NON</tt></td>
	<td align="left" valign="top" width="85%">No access is available to collected information.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>DSP</tt></td>
	<td align="left" valign="top" width="85%">The policy contains at least one dispute-resolution mechanism.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>COR</tt></td>
	<td align="left" valign="top" width="85%">Violations of this policy will be corrected.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>CURa</tt></td>
	<td align="left" valign="top" width="85%">The data is used for completion of the current activity.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>ADMa</tt></td>
	<td align="left" valign="top" width="85%">The data is used for site administration.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>DEVa</tt></td>
	<td align="left" valign="top" width="85%">The data is used for research and development.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>TAIa</tt></td>
	<td align="left" valign="top" width="85%">The data is used for tailoring the site.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>OUR</tt></td>
	<td align="left" valign="top" width="85%">The data is given to ourselves and our agents.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>BUS</tt></td>
	<td align="left" valign="top" width="85%">Our business practices specify how long the data will be kept.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>IND</tt></td>
	<td align="left" valign="top" width="85%">The data will be kept indefinitely.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>UNI</tt></td>
	<td align="left" valign="top" width="85%">Unique identifiers are collected.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>COM</tt></td>
	<td align="left" valign="top" width="85%">Computer information is collected.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>NAV</tt></td>
	<td align="left" valign="top" width="85%">Navigation and clickstream data is collected.</td>
</tr>
<tr>
	<td align="left" valign="top" width="15%"><tt>INT</tt></td>
	<td align="left" valign="top" width="85%">Interactive data is collected.</td>
</tr>
</table>
</div>

<br />

The compact policy is sent by the Web server along with the cookies it describes. For more information, see the P3P deployment guide at <a href="http://www.w3.org/TR/p3pdeployment">http://www.w3.org/TR/p3pdeployment</a>.
<br /><br />

<!-- "Policy Evaluation" section of privacy policy -->
<h2>Policy Evaluation</h2>
Microsoft Internet Explorer 6 will evaluate this policy's compact policy whenever it is used with a cookie. The actions IE will take depend on what privacy level the user has selected in their browser (Low, Medium, Medium High, or High); the default is Medium. In addition, IE will examine whether the cookie's policy is considered satisfactory or unsatisfactory, whether the cookie is a session cookie or a persistent cookie, and whether the cookie is used in a first-party or third-party context. This section will attempt to evaluate this policy's compact policy against Microsoft's stated behavior for IE6.
<br /><br />
<strong>Note:</strong> this evaluation is currently experimental and should not be considered a substitute for testing with a real Web browser.
<br /><br />
<strong>Satisfactory policy</strong>: this compact policy is considered <em>satisfactory</em> according to the rules defined by Internet Explorer 6. IE6 will accept cookies accompanied by this policy under the High, Medium High, Medium, Low, and Accept All Cookies settings.