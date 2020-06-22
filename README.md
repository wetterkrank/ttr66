#Plugin and theme for ttr66.ru

**customify-ttr66 theme (child theme for Customify) functions**
- Display category archive pages (custom taxonomy 'Product groups')
- Display single post pages (custom post type 'Brand')
- Disable comments
- Extra styling

**ttr66 plugin functions**
- Display a dashboard widget with quick links
- Import pricelist exported from 1C Торговля-Склад in the following XML format:
```
<root>
	<Vendor Id="39105" Name="Conte">
		<Tovar Id="73879" Name="13с84 носки Bamboo" Price="90"/>
	</Vendor>
</root>
```

**Dependencies**
- Customify theme (required)
- PODS plugin (required) -- registers custom post type and taxonomy, add admin menu section and metaboxes
- Datatables.net jQuery plugin (optional) -- makes the Brand pricelist tables sortable, adds Excel download button

**Other plugins used**
- Simple Page Ordering
- WPForms Lite
- WP Mail SMTP
- FileBird Lite
- Kadence Blocks
- Loginizer
- Disable XML-RPC

**Maintenance plugins**
- CIO Custom Fields Importer + WP All Import
- Crop Thumbnails
- Regenerate Thumbnails
- WP-Sweep
