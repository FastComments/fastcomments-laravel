<div id="{{ $containerId }}"></div>
<script>
(function() {
    var targetEl = document.getElementById(@js($containerId));
    var config = @js($widgetConfig);
    var constructorName = @js($constructorName);
    var maxRetries = 100;
    var retries = 0;
    function init() {
        if (window[constructorName]) { window[constructorName](targetEl, config); }
        else if (retries++ < maxRetries) { setTimeout(init, 50); }
        else { console.warn('FastComments: ' + constructorName + ' failed to load after ' + maxRetries + ' retries.'); }
    }
    if (!document.querySelector('script[data-fc-widget="' + constructorName + '"]')) {
        var s = document.createElement('script');
        s.src = @js($scriptSrc); s.async = true;
        s.setAttribute('data-fc-widget', constructorName);
        s.onload = init;
        s.onerror = function() { console.warn('FastComments: failed to load script ' + s.src); };
        document.head.appendChild(s);
    } else {
        init();
    }
})();
</script>
