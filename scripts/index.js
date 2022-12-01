const navBtn = document.querySelector('.humburger');
const smallScreenMenu = document.querySelector('.nav-links-small-screen')
const overlay =  document.querySelector('.overlay');

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
const allPdfImageElements = document.querySelectorAll(".pdf-img");

//add images 
// console.log(allPdfImageElements)

//CHANGE CURRENT IMAGE/PDF PAGE
function changeImgUrl(){
  allPdfImageElements.forEach(pdfImage=>{
    const eachMainContainer = pdfImage.parentElement.parentElement.parentElement;
    // console.log(eachMainContainer);
    const img1 = eachMainContainer.querySelector('.pdf-img');
    const img1Url = img1.src;
    // console.log(img1Url)
    const nextImgBtn = eachMainContainer.querySelector('#next-page');
    // console.log(nextImgBtn);
    const prevImgBtn = eachMainContainer.querySelector('#prev-page');
    const pageNumberElement = eachMainContainer.querySelector('#page-num')

    const totalPages = eachMainContainer.querySelector("#page-count").innerText;
   
    //show next image/page;
    let pageNumber = pageNumberElement.innerText; //counter
    nextImgBtn.addEventListener('click', ()=>{
      if(pageNumber<=totalPages){
      if(pageNumber<totalPages){
        pageNumber++
      }
      else{
        pageNumber = 1;
      }
      if(pageNumber<=10){
      var newImgSrc = img1.src.split(''); 
      // console.log(newImgSrc.length)
      newImgSrc[newImgSrc.length-5]=pageNumber;
      // console.log(newImgSrc);
      newImgSrc = newImgSrc.join('');
      // console.log(newImgSrc)
      img1.src = newImgSrc;
      pageNumberElement.innerText = pageNumber;
      // console.log(newImgSrc);
      }
      else{
        // alert("false")
        //here will handle the cases where the the images are more than 11
        console.log(newImgSrc)
      }

      pageNumberElement.innerText = pageNumber;
     
    }
  })
   
  //show Previous image/page
  prevImgBtn.addEventListener('click', ()=>{
    if(pageNumber<=totalPages){
    if(pageNumber>1){
      pageNumber--;
    }  
    let newImgSrc = img1.src.split(''); 
    // console.log(newImgSrc.length)
    newImgSrc[newImgSrc.length-5]=pageNumber;
    // console.log(newImgSrc);
    newImgSrc = newImgSrc.join('');
    // console.log(newImgSrc)
    img1.src = newImgSrc;
    pageNumberElement.innerText = pageNumber;
  }
})

  })
}
changeImgUrl()

// VIDEO PLAYER

// Select the HTML5 video
const video = document.querySelector("#video")
// set the pause button to display:none by default
document.querySelector(".fa-pause").style.display = "none"
// update the progress bar
video.addEventListener("timeupdate", () => {
    let curr = (video.currentTime / video.duration) * 100
    if(video.ended){
        document.querySelector(".fa-play").style.display = "block"
        document.querySelector(".fa-pause").style.display = "none"
    }
    document.querySelector('.inner').style.width = `${curr}%`
})
// pause or play the video
const play = (e) => {
  // Condition when to play a video
  if(video.paused){
      document.querySelector(".fa-play").style.display = "none"
      document.querySelector(".fa-pause").style.display = "block"
      video.play()
  }
  else{
      document.querySelector(".fa-play").style.display = "block"
      document.querySelector(".fa-pause").style.display = "none"
      video.pause()
  }
}
// trigger fullscreen
const fullScreen = (e) => {
  e.preventDefault()
  video.requestFullscreen()
}
// download the video
// const download = (e) => {
//   e.preventDefault()
//   let a = document.createElement('a')
//   a.href = video.src 
//   a.target = "_blank"
//   a.download = ""
//   document.body.appendChild(a)
//   a.click()
//   document.body.removeChild(a)
// }
// rewind the current time
// const rewind = (e) => {
//   video.currentTime = video.currentTime - ((video.duration/100) * 5)
// }
// forward the current time
// const forward = (e) => {
//   video.currentTime = video.currentTime + ((video.duration/100) * 5)
// }