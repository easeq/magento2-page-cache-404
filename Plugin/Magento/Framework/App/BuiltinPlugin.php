<?php

namespace HS\PageCache404\Plugin\Magento\Framework\App;

use Magento\Framework\App\Response\Http as ResponseHttp;

class BuiltinPlugin extends \Magento\PageCache\Model\App\FrontController\BuiltinPlugin
{
    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\Response\Http
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->version->process();
        if (!$this->config->isEnabled() || $this->config->getType() != \Magento\PageCache\Model\Config::BUILT_IN) {
            return $proceed($request);
        }
        $result = $this->kernel->load();
        if ($result === false || ($result instanceof ResponseHttp && $result->getStatusCode() == 404)) {
            $result = $proceed($request);
            if ($result instanceof ResponseHttp) {
                $this->addDebugHeaders($result);
                $this->kernel->process($result);
            }
        } else {
            $this->addDebugHeader($result, 'X-Magento-Cache-Debug', 'HIT', true);
        }
        return $result;
    }
}
