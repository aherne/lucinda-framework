<?php
class TestCacheable extends CacheableDriver {
    protected function setTime() {
        return time();
    }

    protected function setEtag() {
        return "asd";
    }
}