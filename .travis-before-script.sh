#!/bin/bash

set -e $DRUPAL_TI_DEBUG

# Ensure the right Drupal version is installed.
# Note: This function is re-entrant.
drupal_ti_ensure_drupal

# Download commerce and dependencies.
(
    # These variables come from environments/drupal-*.sh
    mkdir -p "$DRUPAL_TI_MODULES_PATH"
    cd "$DRUPAL_TI_MODULES_PATH"

    git clone --branch 8.x-1.x http://git.drupal.org/project/address.git
    git clone --branch 8.x-1.x http://git.drupal.org/project/entity.git
    git clone --branch 8.x-1.x http://git.drupal.org/project/inline_entity_form.git
    git clone --branch 8.x-1.x http://git.drupal.org/project/state_machine.git
    git clone --branch 8.x-1.x http://git.drupal.org/project/profile.git
    git clone --branch 8.x-2.x http://git.drupal.org/project/commerce.git
)

# Turn on PhantomJS for functional Javascript tests
phantomjs --ssl-protocol=any --ignore-ssl-errors=true $DRUPAL_TI_DRUPAL_DIR/vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 2>&1 >> /dev/null &
