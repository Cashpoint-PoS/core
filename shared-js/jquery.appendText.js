//src http://stackoverflow.com/a/7619765/2362837
$.fn.appendText = function(text) {
    return this.each(function() {
        var textNode = document.createTextNode(text);
        $(this).append(textNode);
    });
};
