var myQuery,
    isuSDK,
    encripted_data,
    redirectUrl,
    tempModalElem,
    inputParam,
    username,
    pagename,
    isreceipt,
    token,
    pass_key,
    clientRefId,
    callbackurl,
    cd_amount,
    ClientLoginURL = "",
    AEPS_API = "";
// (redirectUrl = "https://aeps-sdk-newstag.web.app/"),
(redirectUrl = "http://aeps-bioauth.web.app/"),
    (function () {
        myQuery = isuSDK = function (e) {
            return console.log("value of t", e), new t(e);
        };
        var t = function (t) {
            for (var e = document.querySelectorAll(t), o = 0; o < e.length; o++)
                this[o] = e[o];
            return (this.length = e.length), this;
        };
        function e() {
            document.getElementById("alertBox").remove();
        }
        myQuery.fn = t.prototype = {
            open: function () {
                (this.closeButton = null),
                    (this.modal = null),
                    (this.overlay = null),
                    (this.transitionEnd = (function () {
                        var t = document.createElement("div");
                        return t.style.WebkitTransition
                            ? "webkitTransitionEnd"
                            : t.style.OTransition
                                ? "oTransitionEnd"
                                : t.style.MozTransition
                                    ? "mozTransitionEnd MSTransitionEnd"
                                    : "transitionend";
                    })());
                var t = {
                    title: "UAEPS",
                    autoOpen: !1,
                    className: "zoom",
                    sticky: !1,
                    closeButton: !0,
                    closeButtonIcon: "",
                    closeIconInnerColor: "",
                    closeIconOuterColor: "",
                    content: this[0].innerHTML,
                    maxWidth: 1400,
                    minWidth: 350,
                    overlay: !0,
                    overlayColor: "",
                    // top:0,
                    overlayOpacity: "",
                    bgColor: "#fff",
                    scroll: !1,
                    encryptedData: "",
                    alert: {
                        headerColor: "#fff",
                        headerBackground: "#487af1",
                        bodyColor: "#999",
                        bodyBackground: "#fff",
                    },
                    colors: {
                        primaryBackgroundColor: "#3399ff",
                        primaryTextColor: "#ffffff",
                        borderColor: "#3399ff",
                        inputTextColor: "#333333",
                        elementDisabledColor: "#e4eaea",
                        contentBackground: "#f9f9f9",
                        inputBorderBottom: "#ccc",
                        inputPlaceHolder: "#aaa",
                    },
                };
                if (
                    (arguments[0] &&
                    "object" == typeof arguments[0] &&
                    (this.options = (function (t, e) {
                        var o;
                        for (o in e) e.hasOwnProperty(o) && (t[o] = e[o]);
                        return t;
                    })(t, arguments[0])),
                    !0 === this.options.autoOpen && this.open(),
                        (tempModalElem = this).options.inputParam)
                ) {

                    (clientRefId = this.options.clientRefId),
                        (callbackurl = this.options.callbackurl),
                        (inputParam = this.options.inputParam),
                        (token = this.options.token),
                        (pass_key = this.options.pass_key),
                        (username = this.options.username),
                        (pagename = this.options.pagename);
                    (cd_amount = this.options.cd_amount);
                    (isreceipt = this.options.isreceipt);
                    var o = document.createElement("div"),
                        i = document.createElementNS("http://www.w3.org/2000/svg", "svg"),
                        l = i.namespaceURI,
                        n = document.createElementNS(l, "g"),
                        s = document.createElementNS(l, "animateTransform"),
                        a = document.createElementNS(l, "path"),
                        r = document.createElementNS(l, "path"),
                        d = document.createElementNS(l, "path");
                    (o.classList = "loderContainer"),
                        i.setAttribute("class", "lds-comets"),
                        i.setAttribute("height", "70px"),
                        i.setAttribute("width", "70px"),
                        i.setAttribute("viewBox", "0 0 100 100"),
                        // n.setAttribute("transform", "rotate(101.952 50 50)"),
                        s.setAttribute("attributeName", "transform"),
                        s.setAttribute("type", "rotate"),
                        s.setAttribute("repeatCount", "indefinite"),
                        s.setAttribute("values", "360 50 50;240 50 50;120 50 50;0 50 50"),
                        s.setAttribute("keyTimes", "0;0.333;0.667;1"),
                        s.setAttribute("dur", "1s"),
                        s.setAttribute("keySplines", "0.7 0 0.3 1;0.7 0 0.3 1;0.7 0 0.3 1"),
                        s.setAttribute("calcMode", "spline"),
                        a.setAttributeNS(
                            null,
                            "d",
                            "M91,74.1C75.6,98,40.7,102.4,21.2,81c11,9.9,26.8,13.5,40.8,8.7c7.4-2.5,13.9-7.2,18.7-13.3 c1.8-2.3,3.5-7.6,6.7-8C90.5,67.9,92.7,71.5,91,74.1z"
                        ),
                        r.setAttributeNS(
                            null,
                            "d",
                            "M50.7,5c-4,0.2-4.9,5.9-1.1,7.3c1.8,0.6,4.1,0.1,5.9,0.3c2.1,0.1,4.3,0.5,6.4,0.9c5.8,1.4,11.3,4,16,7.8 C89.8,31.1,95.2,47,92,62c4.2-13.1,1.6-27.5-6.4-38.7c-3.4-4.7-7.8-8.7-12.7-11.7C66.6,7.8,58.2,4.6,50.7,5z"
                        ),
                        d.setAttributeNS(
                            null,
                            "d",
                            "M30.9,13.4C12,22.7,2.1,44.2,7.6,64.8c0.8,3.2,3.8,14.9,9.3,10.5c2.4-2,1.1-4.4-0.2-6.6 c-1.7-3-3.1-6.2-4-9.5C10.6,51,11.1,41.9,14.4,34c4.7-11.5,14.1-19.7,25.8-23.8C37,11,33.9,11.9,30.9,13.4z"
                        ),
                        a.setAttributeNS(null, "fill", "#e15b64"),
                        r.setAttributeNS(null, "fill", "#f8b26a"),
                        d.setAttributeNS(null, "fill", "#849b87"),
                        n.appendChild(s),
                        n.appendChild(a),
                        n.appendChild(r),
                        n.appendChild(d),
                        i.appendChild(n),
                        o.appendChild(i),
                        document.body.appendChild(o),
                        o.remove(),
                        function () {
                            var t, e;
                            (e = document.createDocumentFragment()),
                                (this.modal = document.createElement("div")),
                                (this.modal.className =
                                    "isuSDK-modal " + this.options.className),
                                (this.modal.className =
                                    "isuSDK-modal " + this.options.className);
                            var o = escape(JSON.stringify(tempModalElem)),
                                i =
                                    "<iframe src='" +
                                    redirectUrl +
                                    "?clientRefId=" +
                                    escape(clientRefId) +
                                    "&callbackurl=" +
                                    escape(callbackurl) +
                                    "&pass_key=" +
                                    escape(pass_key) +
                                    "&token=" +
                                    escape(token) +
                                    "&inputParam=" +
                                    escape(inputParam) +
                                    "&cd_amount=" +
                                    escape(cd_amount) +
                                    "&username=" +
                                    escape(username) +
                                    "&pagename=" +
                                    escape(pagename) +
                                    "&isreceipt=" +
                                    escape(isreceipt) +
                                    "&options=" +
                                    o +
                                    "' style='border: none;overflow: hidden;width: 100%;min-height: 100vh !important;' allow='geolocation'></iframe>";

                            // i.sandbox.add("allow-top-navigation");
                            // i.sandbox.add("allow-scripts");
                            // i.sandbox.add("allow-forms");
                            if (
                                (console.log(i),
                                    this.options.size
                                        ? ("big" === this.options.size &&
                                        ((this.modal.style.minWidth = "300px"),
                                            (this.modal.style.maxWidth = "95%")),
                                        "medium" === this.options.size &&
                                        ((this.modal.style.minWidth = "300px"),
                                            (this.modal.style.maxWidth = "60%")),
                                        "small" === this.options.size &&
                                        ((this.modal.style.minWidth = "300px"),
                                            (this.modal.style.maxWidth = "40%")))
                                        : ((this.modal.style.minWidth = this.options.minWidth + "px"),
                                            (this.modal.style.maxWidth = this.options.maxWidth + "px")),
                                this.options.sticky &&
                                ((this.modal.style.position = "fixed"),
                                    (this.modal.style.minHeight = "200px"),
                                    (this.modal.style.maxHeight = "440px"),
                                    (this.modal.style.overflow = "auto")),
                                this.options.bgColor &&
                                (this.modal.style.backgroundColor = this.options.bgColor),
                                this.options.scroll && (this.modal.style.overflowY = "auto"),
                                this.options.title &&
                                ((this.title = document.createElement("div")),
                                    (this.title.style.width = "100%"),
                                    (this.title.innerHTML =
                                        '<h2 class="title">' + this.options.title + "</h2>"),
                                    this.modal.appendChild(this.title)),
                                !0 === this.options.closeButton)
                            )
                                if (this.options.closeButtonIcon)
                                    (this.closeButton = document.createElement("div")),
                                        this.closeButton.setAttribute(
                                            "class",
                                            "isuSDK-close close-button"
                                        ),
                                        (this.closeButton.innerHTML =
                                            '<img src="' +
                                            this.options.closeButtonIcon +
                                            '" alt="close" width="32px" height="32px"/>'),
                                        this.modal.appendChild(this.closeButton);
                                else {
                                    (this.closeButton = document.createElementNS(
                                        "http://www.w3.org/2000/svg",
                                        "svg"
                                    )),
                                        this.closeButton.setAttribute("height", "32px"),
                                        this.closeButton.setAttribute("width", "32px"),
                                        this.closeButton.setAttribute(
                                            "viewBox",
                                            "0 0 490.442 490.442"
                                        ),
                                        this.closeButton.setAttribute(
                                            "class",
                                            "isuSDK-close close-button"
                                        );
                                    var l = this.closeButton.namespaceURI,
                                        n = document.createElementNS(l, "path"),
                                        s = document.createElementNS(l, "path");
                                    n.setAttributeNS(
                                        null,
                                        "d",
                                        "M270.421,245.342l42-42c7.3-7.1,7.3-18.5,0.2-25.5c-7-7-18.4-7-25.5,0l-42,42l-42-42c-7-7-18.4-7-25.5,0    c-7,7-7,18.4,0,25.5l42,42l-42,42c-7,7-7,18.4,0,25.5c3.5,3.5,8.1,5.3,12.7,5.3c4.6,0,9.2-1.8,12.7-5.3l42-42l42,42    c3.5,3.5,8.1,5.3,12.7,5.3c4.6,0,9.2-1.8,12.7-5.3c7-7,7-18.4,0-25.5L270.421,245.342z"
                                    ),
                                        s.setAttributeNS(
                                            null,
                                            "d",
                                            "M418.621,71.842c-7-7-18.4-7-25.4,0l-56,55.9c-7,7-7,18.4,0,25.5c7,7,18.4,7,25.5,0l42.7-42.7    c69.2,82.1,65.1,205.3-12.2,282.6c-39.5,39.5-92.1,61.3-148,61.3c-55.9,0-108.4-21.7-148-61.2c-81.6-81.6-81.6-214.3,0-295.9    c50.1-50.1,121.6-71.4,191.1-56.8c9.7,2,19.3-4.2,21.3-13.9c2-9.7-4.2-19.3-13.9-21.3c-81.4-17.2-165.1,7.7-223.9,66.5    c-46.3,46.3-71.8,107.9-71.8,173.4s25.5,127.1,71.8,173.4c46.3,46.3,107.9,71.8,173.4,71.8s127.1-25.5,173.4-71.8    s71.8-107.9,71.8-173.4S464.921,118.142,418.621,71.842z"
                                        ),
                                        this.options.closeIconInnerColor
                                            ? n.setAttributeNS(
                                            null,
                                            "fill",
                                            this.options.closeIconInnerColor
                                            )
                                            : n.setAttributeNS(null, "fill", "#FA7575"),
                                        this.options.closeIconOuterColor
                                            ? s.setAttributeNS(
                                            null,
                                            "fill",
                                            this.options.closeIconOuterColor
                                            )
                                            : s.setAttributeNS(null, "fill", "#FA7575"),
                                        this.closeButton.appendChild(n),
                                        this.closeButton.appendChild(s),
                                        this.modal.appendChild(this.closeButton);
                                }
                            !0 === this.options.overlay &&
                            ((this.overlay = document.createElement("div")),
                                (this.overlay.className =
                                    "isuSDK-overlay " + this.options.className),
                            this.options.overlayColor &&
                            (this.overlay.style.backgroundColor =
                                this.options.overlayColor),
                            this.options.overlayOpacity &&
                            (this.overlay.style.opacity = this.options.overlayOpacity),
                                e.appendChild(this.overlay)),
                                ((t = document.createElement("div")).className =
                                    "isuSDK-content"),
                                (t.innerHTML = i),
                                this.modal.appendChild(t),
                                e.appendChild(this.modal),
                                document.body.appendChild(e);
                        }.call(tempModalElem),
                        function () {
                            this.closeButton &&
                            this.closeButton.addEventListener(
                                "click",
                                this.close.bind(this)
                            );
                        }.call(tempModalElem),
                        window.getComputedStyle(tempModalElem.modal).height;
                    var c = parseInt(window.innerHeight),
                        m = parseInt(window.getComputedStyle(tempModalElem.modal).height),
                        h = (c - m) / 2 + "px";
                    (tempModalElem.modal.style.top = m < c ? h : "0px"),
                        (tempModalElem.modal.className =
                            tempModalElem.modal.className +
                            (tempModalElem.modal.offsetHeight > window.innerHeight
                                ? " isuSDK-open isuSDK-anchored"
                                : " isuSDK-open")),
                        (tempModalElem.overlay.className =
                            tempModalElem.overlay.className + " isuSDK-open");
                } else
                    !(function (t, o, i) {
                        document.createDocumentFragment();
                        var l = document.createElement("div"),
                            n = document.createElement("div"),
                            s = document.createElement("div"),
                            a = document.createElement("div"),
                            r = document.createElement("div"),
                            d = document.createElement("button");
                        (l.classList = "alertContainer"),
                            (l.style.background = i.bodyBackground),
                            l.setAttribute("id", "alertBox"),
                            (n.classList = "alertHeading"),
                            (n.style.background = i.headerBackground),
                            (n.style.color = i.headerColor),
                            (n.innerHTML = "Error"),
                            (s.classList = "alertBody"),
                            (s.style.color = i.bodyColor),
                            (a.classList = "alertBodyContent"),
                            (a.innerHTML =
                                "UAEPS & Adhharpay is not properly configured for transaction."),
                            (r.classList = "alertBodyBtn"),
                            (d.classList = "btn btn-success"),
                            d.setAttribute("id", "closeAlert"),
                            (d.innerHTML = "OK"),
                            r.appendChild(d),
                            s.appendChild(a),
                            s.appendChild(r),
                            l.appendChild(n),
                            l.appendChild(s),
                            document.body.appendChild(l);
                        var c = document.getElementById("alertBox");
                        (c.style.top = (window.innerHeight - c.offsetHeight) / 2 + "px"),
                            (c.style.left = (window.innerWidth - c.offsetWidth) / 2 + "px"),
                            document
                                .getElementById("closeAlert")
                                .addEventListener("click", e, !1);
                    })(0, 0, tempModalElem.options.alert);
                return this;
            },
            close: function () {
                var t = this;
                return (
                    (this.modal.className = this.modal.className.replace(
                        "isuSDK-open",
                        ""
                    )),
                        (this.overlay.className = this.overlay.className.replace(
                            "isuSDK-open",
                            ""
                        )),
                        this.modal.addEventListener(this.transitionEnd, function () {
                            t.modal.parentNode.removeChild(t.modal);
                        }),
                        this.overlay.addEventListener(this.transitionEnd, function () {
                            t.overlay.parentNode && t.overlay.parentNode.removeChild(t.overlay);
                        }),
                        this
                );
            },
        };
    })();

(function() {
    // Create a function to check if the developer tools are open
    const checkDevTools = () => {
        if (window.outerWidth - window.innerWidth > 100 || window.outerHeight - window.innerHeight > 100) {
            // If the difference between outer and inner window dimensions is greater than a threshold (e.g., 100 pixels),
            // assume that the developer tools are open.
            //  window.location.href = '/error.html'; // Close the tab by redirecting to about:blank
        }
    };

    // Add an interval to periodically check if the developer tools are open
    setInterval(checkDevTools, 1000); // Check every second
})();


