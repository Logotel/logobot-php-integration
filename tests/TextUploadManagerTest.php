<?php

namespace Logotel\Logobot\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Logotel\Logobot\Exceptions\DataInvalidException;
use Logotel\Logobot\Exceptions\InvalidResponseException;
use Logotel\Logobot\Manager;
use Logotel\Logobot\TextUploadManager;
use PHPUnit\Framework\TestCase;

class TextUploadManagerTest extends TestCase
{
    public function test_class_is_returned_correctly()
    {
        $class = Manager::textUpload();

        $this->assertInstanceOf(TextUploadManager::class, $class);
    }

    /**
     * @dataProvider http_cases
     */
    public function test_upload_is_ok(array $data, int $status_code, array $response_message, ?string $thows)
    {

        $mock = new MockHandler([
            new Response($status_code, [], json_encode($response_message))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $clientMock = new Client(['handler' => $handlerStack]);

        $manager = Manager::textUpload()->setClient($clientMock);

        if($thows) {
            $this->expectException($thows);
        }

        $status = $manager
            ->setApiKey($data["api_key"])
            ->setIdentifier($data["identifier"])
            ->setTitle($data["title"])
            ->setContent($data["content"])
            ->setLink($data["link"])
            ->setPermissions($data["permissions"])
            ->setLanguage($data["language"])
            ->setMetadata($data["metadata"] ?? [])
            ->setDocumentDate($data["document_date"] ?? "")
            ->upload();

        $this->assertEquals(['status' => true], $status);

    }

    public static function http_cases(): array
    {
        return [
            "with valid payload" => [
                "data" => [
                    "api_key" => "123456",
                    "identifier" => "identifier",
                    "title" => "a title",
                    "content" => "Lorem ipsum dolor sit, amet cénsés data eoque erudito ignorant illas invidia parum turpius ullamcó utinam? Cernimus fugiéndus ipsa sagittis stulti suscipiet. Aperiam désérunt discordia fácillimis honestum ille inviti mus pródesset reprehendunt sequátur splendido viam? Adipiscuntur aspériorés cónsóletur facete, iste molita offendit ullamco? Chrysippe delicátá denique facilisi, fugiamus industria laetitia mediocrem metus minuti praeterierunt quamquam quanti scribimus tractavissent. Aute brévitér fore inprobitas liberalitati lorem mágnis paene quaerimus senectutem wisi? Alia consumere efficeretur hórtensió incommoda mutáns neglegit privamur priventur séntiamus vita. 
Cérté corpore do firmissimum, his práecláre praeclarorum tempora tertium! Cuius disputatione dissentiunt eadem efficiendi, fugiendis hac labefactetur malesuada quasi? Animum causam desistunt fórtitudó hunc liberalitati officia omitténdis patiatur posuit putent putet turma? 
áppáreát elaboraret inferiorem intémpérantés ipsam maluisti páriátur pertinaces quaeque sophocles? Aptissimum arcu cupiditáte dare, disputatióni firme mortis placebit, probo sero tórquentur vivi? Caritatem chrysippo continent insolens, magnum omne restat solet videmus! Aénéan aliquid ancillae ómne qui scaevolam? Menandri recordamur referri solent stoicis, sumo tale voluptátibus. Bonis caritatem ceterorum concessum dolemus easque ignota infinitum iustitia laudandis noris pértinacia vivendo vóluptatem? Canes facilisi futurós individua ista, láetámur pátrius répéllat tamque voce. 
Optime ponatur ratio rutrum solé videámus. áudiebámus cursu insátiábiles molestiae tenetur? Alterum bonarum canes degendae, deorum detracta eosdem éxquisitaqué pecunias plerique turpis volumus? 
Adipisci amarissimam erit fierent, nóstrud perspexit significet. Aétatis arbitrer iniurias ipsam itáque omnino! Amentur debemus taciti takimata torquem? Brevi conférébamus incursioné intércapédo intereant, nemore nomini numquid quanto risus, tortor vidit. Abhorreant aequitatem consetetur doceat, gráeco impétu libéro pérséquéris ruant statue suspendisse variis! 
Docti explentur facultas inanes iudico patet ruant sic. Alios amicis facilisi isdem laudatur timidiores véri vi. Corrumpit doctrina durissimis intéllégéré, interrogáre noris omnium práesertim, saluto sollicitare vitae? Cáusám cupiditatum dixit fecisse hominem, illius inculta iucunde quae summam vóluptate! Atomum cura disserendi ei, fama monstret póetae! Aenean árgumentándum complexiones consilia cruciántur didicisse eorum falso honesto ipsa molestiae partiendo reliqui retinere vivéndo? 
Esse firme privátio reici! Admonitionem audaces dominorum féci fieri, fructu gaudeat generibusque locátus nóluisse putet répugnantiumvé sátisfácit! áliquet bella insidiarum misérius praetermissum pueriliter repetitis significet véniré vétérum. Aegritudo definiebas docui emolumento illaberetur, latine medicorum patrius periculis primus queo sermo sólet vélim. 
Arguérént arte lectorem plerisque séiunctum, suo utriusque videmus! Aliquod censet dices dividendó etiámsi, iam médéam minima nostros perfecto, quale quibus quidám stábilem! Aliquos atomi condimentum impedit lobortis, miser optimum temeritate una. Diam feugiat ingeniis omne persequeretur, platone soluta! Causa ferri ignaviamque illo, inprobitas recordámur sin! Cetero dialectica effugiendorum eximiae magnos, nostrud praebeat retinent scribimus véniamus? Disciplina idcirco páráretur quálisque quicquam? 
Graecam homero hymenaeos nunc officii possim pró quamvis tenebo. Animus gessisse inanitaté intérprétum, ipsa legendis liberae pugnare! Adiuvet consectetur disputando illo loquérétur offéndimur pótenti praétérmittatur. 
Illius oritur primum quanto vacuitate? Has loco pácem quisquam, variis vétérum! Brute conducunt depravare disputándum, primórum rutrum? Aliquid cadere divina elegantis expeteremus graecis intellegámus iucunditátem linéam medeam munere référri reque secumque suapte. Caréré eriguntur inane iusto, laetitia mererer mollitiá universas usque? ángore déspicationés dominos élaborarét laudandis litteras plusque quaerimus sunt torquatis? ",
                    "link" => "https://www.example.com",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
                    "metadata" => ["a" => "b", "b" => "c", "c" => "d"],
                    "document_date" => "2020-01-01"
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true
                ],
                "throws" => null
            ],
            "with invalid payload" => [
                "data" => [
                    "api_key" => "",
                    "identifier" => "",
                    "title" => "",
                    "content" => "",
                    "link" => "test",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
                    // "document_date" => "2020-01-01"
                ],
                "status_code" => 200,
                "response_message" => [
                    "status" => true
                ],
                "throws" => DataInvalidException::class
            ],
            "with http error" => [
                "data" => [
                    "api_key" => "123456",
                    "identifier" => "123456",
                    "title" => "a title",
                    "content" => "some text to upload",
                    "link" => "https://www.example.com",
                    "language" => "it",
                    "permissions" => ["a", "b", "c"],
                    "document_date" => "2020-01-01"
                ],
                "status_code" => 500,
                "response_message" => [
                    "error" => "Some error"
                ],
                "throws" => InvalidResponseException::class
            ],
        ];
    }

}
