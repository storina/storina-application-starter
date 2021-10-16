////   disable google api key
Object.defineProperty(HTMLScriptElement.prototype, "__src__", Object.getOwnPropertyDescriptor(HTMLScriptElement.prototype, "src"));
Object.defineProperty(HTMLScriptElement.prototype, "src", {
    configurable: true,
    enumerable: true,
    get() {
        return this.__src__;
    },
    set(new_src) {
        if (!new_src || !(new_src.startsWith("https://maps.googleapis.com/maps/api/js/AuthenticationService.Authenticate") ||
            new_src.startsWith("https://maps.googleapis.com/maps/api/js/QuotaService.RecordEvent")))
            this.__src__ = new_src;
    }
});
///////