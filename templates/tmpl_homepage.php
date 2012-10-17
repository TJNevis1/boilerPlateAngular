<?php 
	require_once '../testClass.php';
	$user = new User();
?>

Homepage HTML.  Full templates fill in the item with the 'ng-view' attribute (seen on index.php).  Partial templates are good to change parts of the content instead of the whole page and can be inside of a template (at the bottom of this page - notice no curly braces)!

<br /><br />

Content of {{item_id}}

Search: <input ng-model="query">

<select ng-model="orderProp">
	<option value="name">Alphabetical</option>
	<option value="age">Newest</option>
</select>

<ul>
  <li ng-cloak ng-repeat="phone in phones | filter:query | orderBy:orderProp">
    {{phone.name}}<br />
    <img ng-src="{{phone.imageUrl}}" width="100" />
    <p>{{phone.snippet}}</p>
  </li>
</ul>

<hr />

<div ng-include src="partial_template"></div>