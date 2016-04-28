var ConfigurationDrawModel =  function( paramsObj ){

    var params = paramsObj;

    var container, camera,
        moduleTexture, moduleMaterial, moduleBaseMaterials, supportMaterial, //groundTexture
        controls,
        light, renderer,  scene;

    var configurationContainer, linesContainer;

    var ground, supportBar, supportBarWidth;

    var _this = this;



// InitDrawModel begin:

    this.init = function(){
        container = $('#configuration' + params.configurationId + ' .renderer-container');

        var containerWidth  = container.width();
        var containerHeight = containerWidth * 0.75;


        scene = new THREE.Scene();
        //scene.fog = new THREE.Fog( 0xffffff, 0.01, 70 ); // blue: 0x66B9FC

        //ambient light
        var amlight = new THREE.AmbientLight( 0x404040 ); // soft white light
        scene.add( amlight );


        camera = new THREE.PerspectiveCamera( 45,containerWidth / containerHeight, 1, 500 );
        //camera.position.z = 5;

        //isometric view
        //var aspect = containerWidth / containerHeight;
        //var d = 15;
        //camera = new THREE.OrthographicCamera( -d * aspect, d * aspect, d-1, - d+1, 1, 500 );
-

        //update renderer on window resize begin
        window.addEventListener( 'resize', onWindowResize, false );

        function onWindowResize(){

            containerWidth  = container.width();
            containerHeight = containerWidth * 0.75;

            camera.aspect = containerWidth / containerHeight;
            camera.updateProjectionMatrix();

            renderer.setSize( containerWidth, containerHeight );

        }
        //end update renderer on window resize begin



        // Load a texture, set wrap mode to repeat
        //moduleTexture = new THREE.TextureLoader().load( "/modules/mod_configuration/spa/textures/solar-cell.png" );
        moduleTexture = new THREE.TextureLoader().load( "/modules/mod_configuration/spa/textures/newcell.jpg" );
        moduleTexture.wrapS = THREE.RepeatWrapping;
        moduleTexture.wrapT = THREE.RepeatWrapping;
        //moduleTexture.repeat.set(5, 8);

        moduleMaterial = new THREE.MeshPhongMaterial( {
            map: moduleTexture,
            //color: 0x001D62,
            //overdraw: true,
            transparent: true,
            shininess: 50,
            specular: 0x222222,
            shading: THREE.SmoothShading
        } );


        // Load a texture, set wrap mode to repeat
/*
        groundTexture = new THREE.TextureLoader().load( "/modules/mod_configuration/spa/textures/grass.jpg" );
        groundTexture.wrapS = THREE.RepeatWrapping;
        groundTexture.wrapT = THREE.RepeatWrapping;
        groundTexture.repeat.set(100, 100);
*/

        //var groundMaterial = new THREE.MeshPhongMaterial( {
        var groundMaterial = new THREE.MeshLambertMaterial( {
            //map: groundTexture,
            color: 0xC1C4BE
        } );


        // module materials begin:
        moduleBaseMaterials = [
            new THREE.MeshBasicMaterial({ color: 0xE0E0E0 }),
            new THREE.MeshBasicMaterial({ color: 0xE0E0E0 }),
            new THREE.MeshBasicMaterial({ color: 0x000000 }),
            new THREE.MeshBasicMaterial({ color: 0xE0E0E0 }),
            new THREE.MeshBasicMaterial({ color: 0xE0E0E0 }),
            new THREE.MeshBasicMaterial({ color: 0xE0E0E0 })
        ];
        //end module materials


        //support material
        supportMaterial = new THREE.MeshBasicMaterial( {
            color: 0x979797,
            shading: THREE.SmoothShading
        } );


        //ground
        ground = new THREE.Mesh( new THREE.BoxGeometry(), groundMaterial );
        ground.position.y = 0;
        ground.receiveShadow = true;
        scene.add( ground );
        //end ground

        configurationContainer = new THREE.Object3D();
        linesContainer = new THREE.Object3D();

        //"base" for supports
        supportBar = new THREE.Mesh(
            new THREE.BoxGeometry(),
            supportMaterial
        );

        scene.add( configurationContainer );


        renderer = Detector.webgl ? new THREE.WebGLRenderer({'antialias':true, preserveDrawingBuffer: true }) : new THREE.CanvasRenderer();
        renderer.setSize( containerWidth, containerHeight);
        renderer.setClearColor(0xffffff, 1); //fill bg with color
        //renderer.shadowMap.enabled = true;


        //light begin:
        /*light = new THREE.DirectionalLight(0xFFFFFF);
        light.position.set(0, 1, 1);
        light.target.position.set(0, 1, -1);
        light.castShadow = true;

        scene.add(light);*/

        light = new THREE.DirectionalLight(0xdfebff, 1);
        light.position.set(0, 100, 60);
        light.position.multiplyScalar(1);

        light.castShadow = true;

        light.shadow.mapSize.width = 512;
        light.shadow.mapSize.height = 512;

        var d = 100;

        light.shadow.camera.left = -d;
        light.shadow.camera.right = d;
        light.shadow.camera.top = d;
        light.shadow.camera.bottom = -d;

        light.shadow.camera.far = 1000;
        //light.shadow.darkness = 0.2;

        scene.add(light);

        //scene.add( new THREE.CameraHelper( light.shadow.camera ) );// some help object
        //end light


        //mouse control in 3 lines :) awesome!
        controls = new THREE.OrbitControls( camera, container[0] );
        controls.addEventListener( 'change', function(){ renderer.render(scene, camera) } );
        controls.maxPolarAngle = Math.PI/2;
        controls.enableKeys = false;

        container.html(renderer.domElement);


        var render = function () {
            //requestAnimationFrame( render );
            setTimeout(render,125);
            controls.update();

            renderer.render(scene, camera);
        };

        render();

    };

//--- end Init ----------------------------------------------------------------------------------------------------------------------------



    this.drawModel = function(){


        // ! ВСЕ РАЗМЕРЫ ВВЕДЕННЫЕ ПОЛЬЗОВАТЕЛЕМ ДЕЛИМ НА 1000.

        var tableHeight = params.tableHeight / 1000;
        var tableWidth = params.tableWidth / 1000;
        var distanceToGround = params.distanceToGround / 1000;
        var B = params.B/1000;
        var H = params.H/1000;
        var angleRad = parseInt(params.tableAngle) * Math.PI / 180;
        var supportsInterval = params.supports.interval/1000;



        //ground
        var groundDepth = B + 5;
        var groundWidth = params.L/1000 + 5;
        var groundHeight = 0.05;
        ground.geometry = new  THREE.BoxGeometry( groundWidth, groundHeight, groundDepth);
        ground.position.z = -B/2;
        ground.position.y = -groundHeight;



        // clear old configuration model:
        configurationContainer.children = [];

        // clear old lines:
        linesContainer.children = [];

        var modulesContainer = new THREE.Object3D();
        var supportsContainer = new THREE.Object3D();

        //-------------------------------------------------------------------------------------------------------------------------------------------


        // module begin:
        var module = new THREE.Object3D();

        var moduleSize = {
            height: params.moduleHeight / 1000,
            width: params.moduleWidth / 1000,
            depth: params.moduleDepth / 1000
        };

        //change module orientation
        if (params.moduleOrientation == 'horizontal'){
            moduleTexture.repeat.set(4, 6);
        }
        else{
            moduleTexture.repeat.set(3, 9);
        }
        moduleMaterial.map = moduleTexture;


        var moduleBase = new THREE.Mesh(
            new THREE.BoxGeometry( moduleSize.width, moduleSize.depth, moduleSize.height, 1,1,1 ),
            new THREE.MeshFaceMaterial( moduleBaseMaterials )
            //new THREE.MeshBasicMaterial({ color: 0xE0E0E0 })
        );
        moduleBase.castShadow = true;

        var moduleTopFace = new THREE.Mesh(
                new THREE.PlaneGeometry( moduleSize.width-0.05, moduleSize.height-0.05, 1, 1),
                moduleMaterial
        );
        moduleTopFace.position.y += moduleSize.depth - moduleSize.depth/2 + 0.001; // + 0.001 - чтобы панель с ячейками была чуть-чуть выше "основы"
        moduleTopFace.rotation.x = -90 * Math.PI / 180;
        module.add(moduleBase, moduleTopFace);

        // end module --------------------------------------------------------------------------------------------------------------------------------


        //рельса, на которой лежит ряд модулей
        supportBarWidth = params.supports.width/1000;
        var tableFrameBarPositionOffset = moduleSize.height*0.3; //30% of height from center  [ 20% | < 30% * 30% > | 20% ]
        //end: рельса, на которой лежит ряд модулей


        // generate the "table" begin:

        // модули добавляем в "контейнер".
        // при добавлении в контейнер, середина модуля == середине контейнера, поєтому сдвинем каждай модуль влево на половину ширины, и вверх на половину высоты.
        // модули сдвигаютя относительно центра "контейнера" - таким образом, по горизонтали "нулевая точка" будет по центру стола:
        // по оси Х: сдвигаем каждый модуль влево на половину ширины стола, а затем вправо на половину ширины модуля                |_|_|*|_|_|
        var offsetX = -( tableWidth / 2 ) + moduleSize.width/2;

        //                                                                                                                    |_|_|_|_|_|_|_|
        //                                                                                                                    |_|_|_|_|_|_|_|
        //по вертикали "нулевая точка" будет в самом низу: так удобнее - относительно нее будет поворачиваться столешница     |_|_|_|*|_|_|_|
        var offsetZ = moduleSize.height/2;
        var moduleInterval = params.r/1000;

        var longSupportBar = supportBar.clone();
        longSupportBar.geometry = new THREE.BoxGeometry(tableWidth, supportBarWidth, supportBarWidth, 1,1,1);

        for ( var row = 0; row < params.rows; row++ ){
            for ( var col = 0; col <  params.modulesCount; col++ ){
                var moduleClone = module.clone();

                moduleClone.position.x = col * (moduleSize.width + moduleInterval);
                moduleClone.position.x += offsetX; //move it left for half of table width

                moduleClone.position.z = -( row * (moduleSize.height + moduleInterval) );
                moduleClone.position.z -= offsetZ;

                modulesContainer.add( moduleClone );
            }

            //add long horizontal supports |-----|
            for ( var index = 0; index<2; index++ ) {
                var barOffsetY = ( index == 0 ) ? -tableFrameBarPositionOffset : tableFrameBarPositionOffset;
                barOffsetY = ( barOffsetY + moduleSize.height/2 ) + (moduleSize.height * row); //вот тут я уже сам запутался

                var bar = longSupportBar.clone();
                bar.position.set( 0, -moduleSize.depth, -barOffsetY );
                modulesContainer.add( bar );
            }

        }//end for


        //move table up above the ground
        modulesContainer.position.set( 0, distanceToGround, 0 );

        //rotate for angle which was set by user
        modulesContainer.rotation.x = angleRad;

        configurationContainer.add(modulesContainer);

        // end generate the "table" -----------------------------------------------------------------------------------------------------------------


        //LINES begin:

        if ( params.isShowLines ){

            var material = new THREE.LineBasicMaterial({
                color: 0x000055
            });

            //length L line

            var linePos = {
                x: tableWidth/2,
                y: H + 1,
                z: -B
            };

            var geometry = new THREE.Geometry();
            geometry.vertices.push(
                new THREE.Vector3( -linePos.x, linePos.y+0.25, linePos.z ),
                new THREE.Vector3( -linePos.x, linePos.y-0.75, linePos.z ),

                new THREE.Vector3( -linePos.x, linePos.y, linePos.z ),
                new THREE.Vector3( linePos.x, linePos.y, linePos.z ),

                new THREE.Vector3( linePos.x, linePos.y+0.25, linePos.z ),
                new THREE.Vector3( linePos.x, linePos.y-0.75, linePos.z )
            );
            linesContainer.add( new THREE.Line( geometry, material ) );

            var sprite = makeTextSprite('L');
            sprite.position.set( 0.5 , linePos.y + 0.25, linePos.z );
            linesContainer.add( sprite );

            //end L Line


            //height H line
            var hLine = new THREE.Object3D();

            var linePos = {
                x: 0,
                y: H + 0.05,
                z: 0
            };

            var geometry = new THREE.Geometry();
            geometry.vertices.push(

                new THREE.Vector3( linePos.x + 0.25, linePos.y, linePos.z ),
                new THREE.Vector3( linePos.x - 0.5, linePos.y, linePos.z ),

                new THREE.Vector3( linePos.x, linePos.y, linePos.z ),
                new THREE.Vector3( linePos.x, 0.05, linePos.z ),

                new THREE.Vector3( linePos.x + 0.25, 0.05, linePos.z ),
                new THREE.Vector3( linePos.x - 0.5, 0.05, linePos.z )

            );
            var line = new THREE.Line( geometry, material );
            line.rotation.y = 45 * Math.PI/180;
            hLine.add( line );
            hLine.position.set( tableWidth/2 + 0.75, 0, -B - 0.5 );

            var sprite = makeTextSprite('H');
            sprite.position.set( linePos.x, linePos.y/2, linePos.z );
            hLine.add( sprite );

            linesContainer.add( hLine );

            //end H Line


            // B line

            var linePos = {
                x: tableWidth/2 +1,
                y: 0.05,
                z: -B
            };

            var geometry = new THREE.Geometry();
            geometry.vertices.push(

                new THREE.Vector3( linePos.x - 0.5,  linePos.y, 0 ),
                new THREE.Vector3( linePos.x + 0.25, linePos.y, 0 ),

                new THREE.Vector3( linePos.x, linePos.y, 0 ),
                new THREE.Vector3( linePos.x, linePos.y, linePos.z ),

                new THREE.Vector3( linePos.x - 0.5, linePos.y, linePos.z ),
                new THREE.Vector3( linePos.x + 0.25, linePos.y, linePos.z )

            );
            linesContainer.add( new THREE.Line( geometry, material ) );

            var sprite = makeTextSprite('B');
            sprite.position.set( linePos.x + 0.05, linePos.y+0.05, linePos.z/2  );
            linesContainer.add( sprite );

            //end  B Line

            // h line

            var linePos = {
                x: -tableWidth/2,
                y: distanceToGround,
                z: 0.75
            };

            var geometry = new THREE.Geometry();
            geometry.vertices.push(
                new THREE.Vector3( linePos.x, 0.05, linePos.z + 0.25 ),
                new THREE.Vector3( linePos.x, 0.05, linePos.z - 0.5 ),

                new THREE.Vector3( linePos.x, 0.05, linePos.z ),
                new THREE.Vector3( linePos.x, linePos.y, linePos.z ),

                new THREE.Vector3( linePos.x, linePos.y, linePos.z + 0.25 ),
                new THREE.Vector3( linePos.x, linePos.y, linePos.z - 0.5 )
            );
            linesContainer.add( new THREE.Line( geometry, material ) );

            var sprite = makeTextSprite('h', 48);
            sprite.position.set( linePos.x, linePos.y/2, linePos.z + 0.25 );
            linesContainer.add( sprite );
            //end h Line

            scene.add(linesContainer);
        }
        //end LINES-------------------------------------------------------------------------------------------------------------------------


        //draw supports begin:

        var offsetX = -tableWidth/2 + supportBarWidth/2;

        // generate support parts "prototypes", which will be cloned further:

        //support under modules

        var frontSupportHeight = distanceToGround;
        var backSupportHeight = H - supportBarWidth/2;

        var supportUnderModuleOffsetY = ( backSupportHeight - frontSupportHeight ) / 2 + distanceToGround - supportBarWidth;
        var supportUnderModuleOffsetZ = -B/2;

        var supportUnderModule = supportBar.clone(); //sorry for this name. i can't choose good name for it
        supportUnderModule.geometry = new THREE.BoxGeometry(supportBarWidth, supportBarWidth, tableHeight);
        supportUnderModule.position.set(0, supportUnderModuleOffsetY, supportUnderModuleOffsetZ);
        supportUnderModule.rotation.x = angleRad;


        switch ( params.designType ){
            //одноопорная конструкция
            case '1':
                var monoSupportHeight = supportUnderModuleOffsetY;
                var monoSupport = supportBar.clone();
                monoSupport.geometry = new THREE.BoxGeometry(supportBarWidth, monoSupportHeight, supportBarWidth);
                monoSupport.position.set(0, supportUnderModuleOffsetY/2, -B/2);

                //var monoDiagSupportLength = monoSupportHeight/2
                //var monoDiagSupport = supportBar.clone();
                break;

            //двухопорная, по умолчанию
            case '2':
            default:
                //front vertical support
                var frontSupportOffsetY = frontSupportHeight / 2;

                var frontSupport = supportBar.clone();
                frontSupport.geometry = new THREE.BoxGeometry(supportBarWidth, frontSupportHeight, supportBarWidth);
                frontSupport.position.set(0, frontSupportOffsetY, -supportBarWidth);
                frontSupport.castShadow = true;

                //back vertical support prototype
                var backSupportOffsetY = backSupportHeight / 2;
                var backSupportOffsetZ = -B + supportBarWidth / 2;

                var backSupport = supportBar.clone();
                backSupport.geometry = new THREE.BoxGeometry(supportBarWidth, backSupportHeight, supportBarWidth);
                backSupport.position.set(0, backSupportOffsetY, backSupportOffsetZ);


                //"bottom horizontal" support
                var bottomHSupport = supportBar.clone();
                bottomHSupport.geometry = new THREE.BoxGeometry(supportBarWidth, supportBarWidth, B - supportBarWidth/2);
                bottomHSupport.position.set(0, distanceToGround / 2, supportUnderModuleOffsetZ);
                bottomHSupport.reciveShadows = true;
                bottomHSupport.castShadows = true;

/*
                // back "diagonal" support |\|\|  <--  \ <--
                var backDiagonalSupport = supportBar.clone();
                var backDiagSmallerCathetus = H - (distanceToGround);
                var backDiagSuppWidth = Math.sqrt(Math.pow(supportsInterval, 2) + Math.pow(backDiagSmallerCathetus, 2));

                backDiagonalSupport.geometry = new THREE.BoxGeometry(backDiagSuppWidth, supportBarWidth, supportBarWidth);
                backDiagonalSupport.rotation.z = Math.acos(supportsInterval / backDiagSuppWidth); //angle in radians;
                backDiagonalSupport.position.set(-supportsInterval / 2, H / 2, -B + supportBarWidth/2 );
*/
                break;
        }//end switch

        //end generate support parts "prototypes"


        //create support "instances", and add it to supports container
        for ( var i=0; i < params.supports.count; i++ ) {

            var xPos = (supportsInterval * i) + offsetX;


            //одноопорная кострукция
            if (params.designType == '1'){
                var monoSupportClone = monoSupport.clone();
                monoSupportClone.position.x = xPos;
                supportsContainer.add( monoSupportClone );
            }

            //двухопорная кострукция, по дефолту
            else {

                //front supports
                var frontSupportClone = frontSupport.clone();
                frontSupportClone.position.x = xPos;

                //back supports
                var backSupportClone = backSupport.clone();
                backSupportClone.position.x = xPos;

                //bar which connect front support with back support |____|
                var bottomHSupportClone = bottomHSupport.clone();
                bottomHSupportClone.position.x = xPos;

                supportsContainer.add(
                    frontSupportClone,
                    backSupportClone,
                    bottomHSupportClone
                );

/*
                // "diagonal" supports
                if ( i > 0 ) {  // skip first
                    var backDiagonalSupportClone = backDiagonalSupport.clone();
                    backDiagonalSupportClone.position.x += xPos;
                    supportsContainer.add(backDiagonalSupportClone);
                }
*/
            }


            //support under modules
            var supportUnderModuleClone = supportUnderModule.clone();
            supportUnderModuleClone.position.x = xPos;
            supportsContainer.add( supportUnderModuleClone );

        }

        configurationContainer.add( supportsContainer );
        //end draw supports

        console.log('drawModel');

        
    };
//--- end drawModel --------------------------------------------------------------------------------------------------------------------------------------------


    this.centerCamera = function(){

        

        //calc camera Z position
        var smallCathetus = ground.geometry.parameters.width/2;
        var angle = camera.fov;
        var angleTan = Math.atan(camera.fov);
        var zPos = angleTan * smallCathetus;
        console.log('zPos',zPos);

        camera.position.copy( configurationContainer.position );
        camera.position.z = zPos;
        camera.position.y += params.H * 2 / 1000;

        return;

        controls.target = new THREE.Vector3(
            configurationContainer.position.x,
            configurationContainer.position.y + params.H/1000,
            configurationContainer.position.z
        );
        camera.position.y = params.H/2/1000 + 1;
        camera.position.z = configurationContainer.position.z + 10;
    }
//--- end centerCamera() --------------------------------------------------------------------------------------------------------------------------------------------


    function makeTextSprite(message, fontsize) {
        if ( fontsize == undefined ) fontsize = 42;
        var ctx, texture, sprite, spriteMaterial;
        var canvas = document.createElement('canvas');
        ctx = canvas.getContext('2d');
        ctx.font = fontsize + "px Arial";

        // setting canvas width/height before ctx draw, else canvas is empty
        canvas.width = ctx.measureText(message).width *3;
        canvas.height = fontsize * 2;

        // after setting the canvas width/height we have to re-set font to apply!?! looks like ctx reset
        ctx.font = fontsize + "px Arial";
        ctx.fillStyle = "0x000055";
        ctx.fillText(message, 0, fontsize);

        texture = new THREE.Texture(canvas);
        texture.repeat.set( 1, 1 );
        texture.minFilter = THREE.LinearFilter; // NearestFilter;
        texture.needsUpdate = true;

        spriteMaterial = new THREE.SpriteMaterial({map : texture});
        sprite = new THREE.Sprite(spriteMaterial);
        return sprite;
    }

};

//end file