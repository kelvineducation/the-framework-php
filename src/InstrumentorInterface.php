<?php

namespace The;

interface InstrumentorInterface
{
    /**
     * @param RequestInterface
     * @return void
     */
    public function startHttpRequest($request);

    /**
     * @param ResponseWriterInterface
     * @return void
     */
    public function endHttpRequest($response);
}
