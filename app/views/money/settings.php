<?php

$hasConnection = $this->getVar('hasConnection');

?>

<?php if (!$hasConnection) { ?>

    <button id="link-button" class="btn btn-success btn-lg">Link Account</button>

<?php } else { ?>

    <p>Already linked</p>

<?php } ?>

<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>

<?php if (!$hasConnection) { ?>

    <script type="text/javascript">

        $('#link-button').click(function () {
            $.post('/money/settings/createLinkToken').done(function (result) {
                result = JSON.parse(result);
                tokenHandle(result.link_token);
            });
        });

        function tokenHandle(token) {
            var handler = Plaid.create({
                // Create a new link_token to initialize Link
                token: token,
                onLoad: function() {
                    // Optional, called when Link loads
                },
                onSuccess: function(token, metadata) {
                    // Send the public_token to your app server.
                    // The metadata object contains info about the institution the
                    // user selected and the account ID or IDs, if the
                    // Account Select view is enabled.
                    // alert(token);
                    $.post('/money/settings/exchangeLinkToken?public_token=' + encodeURI(token));
                },
                onExit: function(err, metadata) {
                    // The user exited the Link flow.
                    if (err != null) {
                        // The user encountered a Plaid API error prior to exiting.
                        console.log(err);
                    }
                    // metadata contains information about the institution
                    // that the user selected and the most recent API request IDs.
                    // Storing this information can be helpful for support.
                    location.reload();
                },
                onEvent: function(eventName, metadata) {
                    // Optionally capture Link flow events, streamed through
                    // this callback as your users connect an Item to Plaid.
                    // For example:
                    // eventName = "TRANSITION_VIEW"
                    // metadata  = {
                    //   link_session_id: "123-abc",
                    //   mfa_type:        "questions",
                    //   timestamp:       "2017-09-14T14:42:19.350Z",
                    //   view_name:       "MFA",
                    // }
                }
            });
            handler.open();
        }

    </script>

<?php } ?>
