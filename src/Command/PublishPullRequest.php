<?php

namespace DS\DevTools\Command;

use DS\DevTools\Repository\RepositoryRepository;
use GitWrapper\GitWrapper;
use StashAPILib\Configuration;
use StashAPILib\StashAPIClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Unirest\Request;

class PublishPullRequest extends Command
{
    protected function configure()
    {
        $this
            ->setName('pr')
            ->setDescription('Publish a new PR to Stash repository.')
            ->setHelp('This command allows you to publish a new PR to Stash repository.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $repository = new RepositoryRepository();
        $gitWrapper = new GitWrapper();
        $currentDirectory = getcwd();
        $git = $gitWrapper->workingCopy($currentDirectory);

        preg_match('/ssh.*\/(.*)\/(.*).git$/', $git->getRemote('origin')['fetch'], $matches);
        list ($fullUrl, $projectKey, $repositorySlug) = $matches;

        $output->writeln([
            'Pull request publisher',
            '============',
            '',
        ]);

        $repo = $repository->findByForkKeyAndSlug($projectKey, $repositorySlug);

        if (null === $repo) {
            $output->writeln('Impossible to find proper repository by projectKey ('.$projectKey.') and repository slug ('.$repositorySlug.')');
            return;
        }

        $fromBranch = $git->getBranches()->head();
        $question = new Question('Please enter "from" branch: [current:'.$fromBranch.']: ', $fromBranch);
        $fromBranch = $helper->ask($input, $output, $question);

        $question = new Question('Please enter "to" branch: [current:'.$fromBranch.']: ', $fromBranch);
        $toBranch = $helper->ask($input, $output, $question);

        $output->writeln([
            '',
            'Initialize Pull Request...',
            $projectKey.':'.$repositorySlug.' ('.$fromBranch.') -> '.$repo['projectKey'].':'.$repo['repositorySlug'].' ('.$toBranch.')',
            ''
        ]);

        $title = trim($git->run(['log -1 --pretty=%B'])->getOutput());
        $question = new Question('Please enter PR title ['.$title.']: ', $title);
        $title = $helper->ask($input, $output, $question);

        var_dump($title);die;

        Configuration::$BASEURI = '';
        Request::auth('', '');
        $stashClient = (new StashAPIClient())->getClient();

//        var_dump($stashClient->create([
//            "title" => "Talking Nerdy",
//            "description" => "It’s a kludge, but put the tuple from the database in the cache.",
//            "state" => "OPEN",
//            "open" => true,
//            "closed" => false,
//            "fromRef" => [
//                "id" => "refs/heads/feature-ABC-123",
//                "repository"=> [
//                    "slug" => "my-repo",
//                    "name" => null,
//                    "project" => [
//                        "key" => "PRJ"
//                    ],
//                ],
//            ],
//            "toRef" => [
//                "id" => "refs/heads/master",
//                "repository" => [
//                    "slug" => "my-repo",
//                    "name" => null,
//                    "project" => [
//                        "key" => "PRJ"
//                    ],
//                ],
//            ],
//            "locked" => false,
//            "reviewers" => [
//                [
//                    "user" => [
//                    "name" => "charlie"
//                    ],
//                ],
//            ]
//        ], $projectKey, $repositorySlug));
    }
}