#!/usr/bin/env bash

echo -e "pre-commit actions"

LOCAL_BRANCH=`git branch | grep \* | cut -d ' ' -f2`
echo -e "Committing \033[0;32m$LOCAL_BRANCH\033[0m"

CHANGED_FILES=`git status -s | cut -c4- | grep "\.php"`
if [[ $CHANGED_FILES ]]; then
    echo -e "find files: ${CHANGED_FILES}"

    set -e

    test -e docker/.env || { cp docker/.env.example docker/.env; };
    export $(egrep -v '^#' docker/.env | xargs)

    CMD='CHANGED_FILES="'${CHANGED_FILES}'" &&
        echo -e "\033[1;33mrun code style fixer\033[0m" &&
            composer code-style:check $CHANGED_FILES &&
        echo -e "\033[1;33mrun code analyzer\033[0m" &&
            composer code-style:analyze $CHANGED_FILES || exit $?';

    bash -c "$CMD"

    git add ${CHANGED_FILES}
fi
