/*
 * This file launches the application by asking Ext JS to create
 * and launch() the Application class.
 */
Ext.application({
    extend: 'GayCanada.Application',

    name: 'GayCanada',

    requires: [
        // This will automatically load all classes in the GayCanada namespace
        // so that application classes do not need to require each other.
        'GayCanada.*'
    ],

    // The name of the initial view to create.
    mainView: 'GayCanada.view.main.Main'
});
