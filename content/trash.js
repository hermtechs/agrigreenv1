const navBtn = document.querySelector('.humburger');
const smallScreenMenu = document.querySelector('.nav-links-small-screen')
// console.log(navBtn)
navBtn.addEventListener('click', ()=>{
    if(navBtn.classList.contains('nav-open')){
        navBtn.classList.remove('nav-open');
        smallScreenMenu.style.transform = 'translateX(-100%)'
    }
    else{
        navBtn.classList.add('nav-open');
        smallScreenMenu.style.transform = 'translateX(0%)'
    }
})
const navLinksSmallScreen = document.querySelectorAll(".nav-links-small-screen a");
navLinksSmallScreen.forEach(link=>{
    link.addEventListener('click', ()=>{
        smallScreenMenu.style.transform = 'translateX(-100%)'
    })
})


//get document from url
// const url1 = "content/Dr. Adnan Identification card 01-11-2022 (1).pdf";
const allCanvas = document.querySelectorAll("#pdf-render");
// const allCanvasArray = [...allCanvas];
// console.log(allCanvasArray);

const pdfUrls= [
  "/content/Dr. Adnan Identification card 01-11-2022 (1).pdf",
  "/content/ملخص السيرة الذاتية للدكتور  عدنان بن فهد بن راشد الرمزاني النعيمي2022-11-01 (2).pdf",
  "/content/Agrigreen foundation 2015& 2016 Activity Report (1).pdf",
  "/content/[KIPA]SIF2020_entryform(_11.13).pdf"
]
 function getDocuments(){
  for(var i=0; i<pdfUrls.length; i++){
  const eachUrl = pdfUrls[i];
  pdfjsLib.getDocument(eachUrl).promise.then((doc)=>{
    // console.log(doc)
    let page1 = doc.getPage(1)
    renderPagesToCanvas(page1)

  })
  }
}
getDocuments();
function renderPagesToCanvas(page1){
  // console.log(page1)
  page1.then(page=>{
  const context = allCanvas[0].getContext('2d');
  // const scale = 1.5;
    allCanvas.forEach(canvas=>{
     const viewport = page.getViewport(1);
    //  console.log(viewport);
    canvas.height = viewport.height;
    canvas.width = viewport.width
    const renderCtx =   {
      canvasContext: context,
      viewport
    }
    page.forEach(eachPage=>{console.log(eachPage)})
    // console.log(canvas.height);
    // console.log(canvas.width);
    })
  })
}


