/******/ (() => {
    // webpackBootstrap
    var __webpack_exports__ = {};
    /*!******************************!*\
  !*** ./resources/js/main.js ***!
  \******************************/
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
    var csrftoken = csrfMeta ? csrfMeta.getAttribute('content') : null;
    var vapidPublicKey = vapidMeta ? vapidMeta.getAttribute('content') : null;
    var debugLogLimit = 120;

    function appendPushDebug(message, meta) {
        var timestamp = new Date().toISOString();
        var line = '[' + timestamp + '] ' + message;

        if (meta) {
            try {
                line += ' | ' + JSON.stringify(meta);
            } catch (error) {
                line += ' | [meta-unserializable]';
            }
        }

        // console.log('[push-debug]', line);

        if (!Array.isArray(window.__pushDebugLines)) {
            window.__pushDebugLines = [];
        }

        window.__pushDebugLines.push(line);

        if (window.__pushDebugLines.length > debugLogLimit) {
            window.__pushDebugLines = window.__pushDebugLines.slice(-debugLogLimit);
        }

        var logElement = document.getElementById('push-debug-log');
        if (logElement) {
            logElement.textContent = window.__pushDebugLines.join('\n');
        }
    }

    function clearPushDebug() {
        window.__pushDebugLines = [];
        var logElement = document.getElementById('push-debug-log');
        if (logElement) {
            logElement.textContent = '';
        }
    }

    function parseJsonOrText(response) {
        return response.text().then(function (text) {
            if (!text) {
                return null;
            }

            try {
                return JSON.parse(text);
            } catch (error) {
                return { raw: text };
            }
        });
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            appendPushDebug('Registering service worker...');
            navigator.serviceWorker
                .register('/sw.js')
                .then(function (registration) {
                    appendPushDebug('Service worker registered.', {
                        scope: registration.scope,
                    });
                })
                ['catch'](function (error) {
                    appendPushDebug('Failed to register service worker.', {
                        error: String(error),
                    });
                });
        });
    } else {
        appendPushDebug('Service worker is not supported by this browser.');
    }

    if (!('Notification' in window)) {
        appendPushDebug('Notification API is not supported by this browser.');
        alert('This browser does not support notifications.');
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    function enablePushNotifications() {
        appendPushDebug('Enable push clicked.');

        if (!('serviceWorker' in navigator)) {
            appendPushDebug('Enable aborted: service worker unsupported.');
            return;
        }

        if (!('Notification' in window)) {
            appendPushDebug('Enable aborted: Notification API unsupported.');
            return;
        }

        appendPushDebug('Current notification permission.', {
            permission: Notification.permission,
        });

        appendPushDebug('Requesting notification permission...');

        Notification.requestPermission()
            .then(function (permission) {
                appendPushDebug('Notification permission result.', {
                    permission: permission,
                });

                if (permission !== 'granted') {
                    var permissionError = 'Notification permission denied. Click Allow to continue subscribe process.';
                    appendPushDebug('Enable aborted: permission not granted.', {
                        error: permissionError,
                    });
                    throw new Error(permissionError);
                }

                appendPushDebug('Waiting for service worker to be ready...');
                return navigator.serviceWorker.ready;
            })
            .then(function (registration) {
                if (!registration) {
                    return;
                }

                appendPushDebug('Service worker is ready.', {
                    scope: registration.scope,
                });

                return registration.pushManager.getSubscription().then(function (subscription) {
                    if (subscription) {
                        appendPushDebug('Existing push subscription found.', {
                            endpoint: subscription.endpoint,
                        });
                        return subscription;
                    }

                    if (!vapidPublicKey) {
                        appendPushDebug('Enable aborted: VAPID key is missing.');
                        alert('VAPID public key is not configured.');
                        return null;
                    }

                    appendPushDebug('Creating new push subscription...');
                    var serverKey = urlBase64ToUint8Array(vapidPublicKey);
                    return registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: serverKey,
                    });
                });
            })
            .then(function (subscription) {
                if (!subscription) {
                    appendPushDebug('No subscription to send to backend.');
                    return;
                }

                appendPushDebug('Subscription ready, sending to backend...');
                subscribe(subscription);
            })
            ['catch'](function (error) {
                appendPushDebug('Enable failed.', {
                    error: String(error),
                });
                var errorMessage = error && error.message ? error.message : String(error);
                alert(errorMessage);
            });
    }

    function disablePushNotifications() {
        appendPushDebug('Disable push clicked.');

        if (!('serviceWorker' in navigator)) {
            appendPushDebug('Disable aborted: service worker unsupported.');
            return;
        }

        if (!csrftoken) {
            appendPushDebug('Disable aborted: CSRF token missing.');
            alert('CSRF token is missing.');
            return;
        }

        navigator.serviceWorker.ready
            .then(function (registration) {
                appendPushDebug('Service worker ready for disable.', {
                    scope: registration.scope,
                });

                return registration.pushManager.getSubscription().then(function (subscription) {
                    if (!subscription) {
                        appendPushDebug('No subscription found to unsubscribe.');
                        return;
                    }

                    appendPushDebug('Found subscription, unsubscribing from browser.', {
                        endpoint: subscription.endpoint,
                    });

                    return subscription.unsubscribe().then(function (unsubscribed) {
                        appendPushDebug('Browser unsubscribe result.', {
                            unsubscribed: unsubscribed,
                        });

                        appendPushDebug('Sending unsubscribe request to backend...');
                        return fetch('/notifications/unsubscribe', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrftoken,
                            },
                            body: JSON.stringify({
                                endpoint: subscription.endpoint,
                            }),
                        })
                            .then(function (response) {
                                return parseJsonOrText(response).then(function (body) {
                                    appendPushDebug('Unsubscribe response received.', {
                                        status: response.status,
                                        ok: response.ok,
                                        body: body,
                                    });

                                    if (!response.ok) {
                                        throw new Error('Unsubscribe request failed with status ' + response.status);
                                    }
                                });
                            })
                            ['catch'](function (error) {
                                appendPushDebug('Disable failed during backend unsubscribe.', {
                                    error: String(error),
                                });
                            });
                    });
                });
            })
            ['catch'](function (error) {
                appendPushDebug('Disable failed.', {
                    error: String(error),
                });
            });
    }

    function subscribe(sub) {
        var key = sub.getKey('p256dh');
        var token = sub.getKey('auth');
        var contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
        var data = {
            endpoint: sub.endpoint,
            public_key: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            auth_token: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
            encoding: contentEncoding,
        };
        if (!csrftoken) {
            appendPushDebug('Subscribe aborted: CSRF token missing.');
            alert('CSRF token is missing.');
            return;
        }

        appendPushDebug('Sending subscribe request to backend...', {
            endpoint: data.endpoint,
            encoding: data.encoding,
        });

        fetch('/notifications/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrftoken,
            },
            body: JSON.stringify(data),
        })
            .then(function (response) {
                return parseJsonOrText(response).then(function (body) {
                    appendPushDebug('Subscribe response received.', {
                        status: response.status,
                        ok: response.ok,
                        body: body,
                    });

                    if (!response.ok) {
                        throw new Error('Subscribe request failed with status ' + response.status);
                    }
                });
            })
            ['catch'](function (error) {
                appendPushDebug('Subscribe failed.', {
                    error: String(error),
                });
            });
    }

    function bindPushButtons() {
        if (window.__pushButtonsBound) {
            return;
        }

        window.__pushButtonsBound = true;
        appendPushDebug('Push button handlers bound.');

        document.addEventListener('click', function (event) {
            var target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            var enableButton = target.closest('#enable-push');
            if (enableButton) {
                enablePushNotifications();
                return;
            }

            var disableButton = target.closest('#disable-push');
            if (disableButton) {
                disablePushNotifications();
                return;
            }

            var clearDebugButton = target.closest('#clear-push-debug');
            if (clearDebugButton) {
                clearPushDebug();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPushButtons);
    } else {
        bindPushButtons();
    }
    /******/
})();
