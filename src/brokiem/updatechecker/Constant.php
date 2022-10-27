<?php

namespace brokiem\updatechecker;

final class Status {
    public const CONNECTION_FAILED = "connection_failed";
    public const NO_UPDATES_FOUND = "no_updates_found";
    public const PLUGIN_NOT_FOUND = "plugin_not_found";
}

final class Option {
    public const LOG_NEW_UPDATE = "log_new_update";
}