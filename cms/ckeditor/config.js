/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.toolbar = [
        { name: 'clipboard', items: [ 'Undo', 'Redo' ] },
        { name: 'editing', items: [ 'Scayt' ] },
        { name: 'links', items: [ 'Link', 'Unlink' ] },
        { name: 'insert', items: [ 'Table', 'Image' ] },
        { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'RemoveFormat' ] },
        { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] }
    ];
    config.extraPlugins = 'uploadimage';
    config.allowedContent = true;
};
