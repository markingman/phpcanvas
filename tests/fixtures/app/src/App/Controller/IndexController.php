<?php

namespace PHPCanvas\Test\App\Controller;

use PHPCanvas\Test\App\Model\ExampleModel;
use PHPCanvas\Test\App\View\Pages\IndexPage;

class IndexController extends AbstractController
{
	protected ExampleModel $ExampleModel;

	public function __invoke(ExampleModel $ExampleModel): void
	{
		$this->ExampleModel = $ExampleModel;
	}

	public function action_default(IndexPage $IndexPage): void
	{
		$this->respond_page($IndexPage);
	}

//	public function action_test(MyModel $MyModel, IndexTestPage $IndexTestPage): void
//	{
//		$IndexTestPage->setData($MyModel->getData());
//
//		$this->Response->html($IndexTestPage());
//	}
//
//	public function action_test(MyModel $MyModel, ViewPage $ViewPage, ViewJSON $ViewJSON): void
//	{
//		$data = $MyModel->getData();
//		if ($his->Request->isAjax()) {
//			$this->Response->json($ViewJSON($data));
//		} else {
//			$this->Response->html($ViewPage($data));
//		}
//	}
//
//	public function responsePage(array $data, ViewPage $ViewPage): void
//	{
//		$this->Response->html($ViewPage->view($data));
//	}


//	public function action_zzz(/*IndexJSON $IndexJSON*/): void
//	{
//		// $this->Response->json($IndexJSON->view());
//
//		exit(<<<__
//<!DOCTYPE html>
//<html lang="en">
//	<head>
//		<title>Test</title>
//	</head>
//	<body>hello, world</body>
//</html>
//__);
//	}
//
//	public function action_test(): void
//	{
//		echo "HERE ";
//		exit(__FILE__);
//	}
}
