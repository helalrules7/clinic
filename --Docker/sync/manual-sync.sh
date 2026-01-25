#!/bin/bash
# Manual sync script - run from host machine
# Usage: ./manual-sync.sh [pull|push|both]

ACTION=${1:-pull}

case $ACTION in
    pull)
        echo "Pulling from remote to local..."
        docker exec clinic_sync /scripts/pull-from-remote.sh
        ;;
    push)
        echo "Pushing from local to remote..."
        docker exec clinic_sync /scripts/push-to-remote.sh
        ;;
    both)
        echo "Full sync: pull then push..."
        docker exec clinic_sync /scripts/pull-from-remote.sh
        docker exec clinic_sync /scripts/push-to-remote.sh
        ;;
    *)
        echo "Usage: $0 [pull|push|both]"
        exit 1
        ;;
esac

echo "Sync complete!"
