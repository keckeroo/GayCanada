Ext.define('GayCanada.model.accounts.Photos', {
	extend: 'Ext.data.Model',

	fields: [
	    { name: 'photoid', 		mapping: 'ID' },
    	{ name: 'imagename', 	mapping: 'Image' },
    	{ name: 'caption', 		mapping: 'Caption' },
    	{ id: 'gallery', name: 'gallery', mapping: 'gallery' },
    	{ name: 'adult', 		mapping: 'Adult' },
    	{ name: 'status', 		mapping: 'SystemStatus' },
    	{ name: 'filename', 	mapping: 'file' },
    	'height', 'width', 
    	{ name: 'thumbnailconfig', mapping: 'thumbnail_config' },
    	{ name: 'filesize', 	mapping: 'size', convert: function(v) { return ( v / 1024).toFixed(); } },
    	'enabled',
    	'flagged',
    	{ name: 'viewsthisweek', mapping: 'views_this_week' },
    	{ name: 'viewstotal', 	mapping: 'views_total' },
    	{ name: 'hotvotes', 	mapping: 'hot_votes' },
    	'converted',
    	{ name: 'date', 		mapping: 'Date' },
    	{ name: 'ts' },
    	{ name: 'showpicture', 	mapping: 'ShowPicture' },
    	{ name: 'defaultphoto', mapping: 'ByDefault' }
	]
});