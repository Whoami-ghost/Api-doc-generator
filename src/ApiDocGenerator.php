<?php

namespace Apidocgenerator;

class ApiDocGenerator
{
    private $controllerDir;
    private $outputFile;

    public function __construct(string $controllerDir, string $outputFile)
    {
        $this->controllerDir = $controllerDir;
        $this->outputFile = $outputFile;
    }

    public function generate()
    {
        $files = $this->scanFiles();
        $docs = [];
        
        foreach ($files as $file) {
            $docBlocks = $this->getDocBlocks($file);
            foreach ($docBlocks as $docBlock) {
                if ($apiDoc = $this->parseDocBlock($docBlock)) {
                    $docs[] = $apiDoc;
                }
            }
        }
        
        $this->saveAsJson($docs);
        $this->displaySuccessMessage($docs);
        return $docs;
    }

    private function scanFiles()
    {
        $files = [];
        $items = scandir($this->controllerDir);
        
        foreach ($items as $item) {
            if ($item === "." || $item === "..") continue;
            $files[] = $this->controllerDir . '\\' . $item;
        }
        
        return $files;
    }

    private function getDocBlocks($filepath)
    {
        $content = file_get_contents($filepath);
        $tokens = token_get_all($content);
        $docBlocks = [];
        
        foreach ($tokens as $token) {
            if (is_array($token)) {
                [$tokenType, $tokenValue] = $token;
                if ($tokenType === T_DOC_COMMENT) {
                    $docBlocks[] = $tokenValue;
                }
            }
        }
        
        return $docBlocks;
    }

    private function parseDocBlock($docBlock)
    {
        $result = [];
        if (preg_match('/@name\s+(.*)/', $docBlock, $match)) {
            $result['name'] = trim($match[1]);
        }
        if (preg_match('/@desc\s+(.*)/', $docBlock, $match)) {
            $result['desc'] = trim($match[1]);
        }
        if (preg_match('/@route\s+(.*)/', $docBlock, $match)) {
            $result['route'] = trim($match[1]);
        }
        if (preg_match('/@method\s+(.*)/', $docBlock, $match)) {
            $result['method'] = trim($match[1]);
        }
        if (preg_match_all('/@param\s+(\S+)\s+(\S+)\s+(.*)/', $docBlock, $matches, PREG_SET_ORDER)) {
            $params = [];
            foreach ($matches as $match) {
                $params[] = [
                    'type' => trim($match[1]),
                    'name' => trim($match[2]),
                    'desc' => trim($match[3])
                ];
            }
            $result['params'] = $params;
        }
        if (preg_match('/@response\s+(\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\})/s', $docBlock, $match)) {
            $jsonResponse = trim($match[1]);
            $decoded = json_decode($jsonResponse, true);
            if ($decoded === null) {
                throw new \Exception("无效的JSON格式: " . $jsonResponse);
            }
            $result['response'] = $decoded;
        }
        return $result;
    }

    private function saveAsJson($docs)
    {
        if (empty($docs)) {
            throw new \Exception("没有找到有效的API文档注释");
        }
        
        $json = json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($json === false) {
            throw new \Exception("JSON编码失败: " . json_last_error_msg());
        }
        
        if (file_put_contents($this->outputFile, $json) === false) {
            throw new \Exception("无法写入文件: " . $this->outputFile);
        }
    }

    private function displaySuccessMessage($docs)
    {
        $count = is_array($docs) ? count($docs) : 0;
        
        echo "============================================\n";
        echo " API文档生成成功！\n";
        echo "============================================\n";
        echo "生成文件: " . $this->outputFile . "\n";
        echo "接口数量: $count\n";
        
        if ($count > 0) {
            echo "包含以下API组：\n";
            
            $groups = [];
            foreach ($docs as $doc) {
                $group = $doc['group'] ?? '未分组';
                if (!in_array($group, $groups)) {
                    $groups[] = $group;
                    echo " - $group\n";
                }
            }
        }
        
        echo "============================================\n";
    }
}
