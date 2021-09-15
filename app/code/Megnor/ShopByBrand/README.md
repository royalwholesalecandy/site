# magento2-shop-by-brand

#Features
<ul>
<li>Link in top Navigation </li>
<li>Automatic assiciate with default manufacturer attribute</li>
<li>Can Re-Sync for New Manufacturer Brand</li>
<li>Any Brand Can be Assign as Featured</li>
<li>Left Side bar Block for Shop By Brands</li>
</ul>

<h2>Composer Installation Instructions</h2>
Add GIT Repository to composer
<pre>
composer config repositories.megnor-magento2-shopbybrand vcs https://github.com/megnor/magento2-shop-by-brand/
</pre>

Since this package is in a development stage, you will need to change the minimum-stability as well to the composer.json file: -
<pre>
"minimum-stability": "dev",
</pre>

After that, need to install this module as follows:
<pre>
  composer require magento/magento-composer-installer
  composer require megnor/shopbybrand
</pre>


<br/>
<h2> Mannual Installation Instructions</h2>
go to Magento2Project root dir 
create following Directory Structure :<br/>
<strong>/Magento2Project/app/code/Megnor/ShopByBrand</strong>
you can also create by following command:
<pre>
cd /Magento2Project
mkdir app/code/Megnor
mkdir app/code/Megnor/ShopByBrand
</pre>



<h3> Enable Megnor/ShopByBrand Module</h3>
to Enable this module you need to follow these steps:

<ul>
<li>
<strong>Enable the Module</strong>
<pre>bin/magento module:enable Megnor_ShopByBrand</pre></li>
<li>
<strong>Run Upgrade Setup</strong>
<pre>bin/magento setup:upgrade</pre></li>
<li>
<strong>Re-Compile (in-case you have compilation enabled)</strong>
	<pre>bin/magento setup:di:compile</pre>
</li>
</ul>
