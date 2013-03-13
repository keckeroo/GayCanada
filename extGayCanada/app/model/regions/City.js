Ext.define('GayCanada.model.regions.City', {
    extend: 'Ext.data.Model',

    fields: [ 
      { name: 'provincecode', mapping: 'Province' },
      { name: 'city', mapping: 'City' },
      { name: 'cityid', mapping: 'CityID' },
      { name: 'population', mapping: 'Population' },
      { name: 'numentries', mapping: 'Num_Entries' },  
      { name: 'numprofiles', mapping: 'Num_Profiles' },
      { name: 'numpersonals', mapping: 'Num_Personals' },
      { name: 'blurb', mapping: 'Information' },
      { name: 'weather', mapping: 'Weather' }   
    ]
});