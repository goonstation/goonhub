<template>
  <div class="background" aria-hidden="true">
    <div class="top">
      <div class="titlecard"></div>
      <div class="right-pane">
        <div class="info-pane">
          <div class="info-buttons">
            <div>Text</div>
            <div>Info</div>
            <div>Map</div>
            <div>Wiki</div>
            <div>GitHub</div>
            <div>Forum</div>
            <div>Bug Report</div>
          </div>
          <div class="info-tabs">
            <div>Status</div>
          </div>
          <div class="info-output">
            <table>
              <tbody>
                <tr>
                  <td>Map:</td>
                  <td>Cogmap</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>Game Mode:</td>
                  <td>secret</td>
                </tr>
                <tr>
                  <td>Time To Start:</td>
                  <td style="color: limegreen">123</td>
                </tr>
                <tr>
                  <td>Server Load:</td>
                  <td>No</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="output-pane"></div>
      </div>
    </div>
    <div class="input">
      <div class="examine"></div>
      <div class="text"></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.background {
  display: flex;
  flex-direction: column;
  position: absolute;
  z-index: 1;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  filter: grayscale(1);
  pointer-events: none;
  user-select: none;

  &:after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
  }

  .top {
    display: flex;
    flex: 1;
  }

  .titlecard {
    flex: 1 1 68%;
    background: url('@img/titlecard-grey.jpg') no-repeat center center;
    background-size: cover;
  }

  .right-pane {
    display: flex;
    flex-direction: column;
    flex: 1 1 32%;
    background: #24272a;
    padding-left: 2px;
    border-left: 1px solid #65676f;
    color: #d3d4d5;

    .info-pane {
      flex: 1 1 25%;
      display: flex;
      flex-direction: column;
      border-left: 1px solid black;
      border-bottom: 1px solid #65676f;

      .info-buttons,
      .info-tabs {
        display: flex;
        gap: 5px;

        > * {
          border-top: 1px solid #65676f;
          border-left: 1px solid #65676f;
          border-right: 1px solid black;
          border-bottom: 1px solid black;
          font-size: 9px;
          text-align: center;
          white-space: nowrap;
        }
      }

      .info-buttons {
        padding: 2px 5px 5px 5px;

        > * {
          width: 47px;
          height: 18px;
          line-height: 18px;
        }

        > :nth-child(2) {
          margin-right: auto;
        }
      }

      .info-tabs {
        padding: 0 2px;

        > * {
          padding: 4px 5px;
          border-top-left-radius: 5px;
          border-top-right-radius: 5px;
          border-bottom: 0;
          line-height: 1;
        }
      }

      .info-output {
        flex: 1;
        margin: 1px 2px 0 2px;
        padding: 0px 2px;
        border-top: 1px solid #a0a0a0;
        border-left: 1px solid #a0a0a0;
        border-right: 1px solid #a0a0a0;
        font-size: 9px;

        td {
          padding: 0 5px 3px 0;
        }
      }
    }

    .output-pane {
      flex: 1 1 75%;
      margin-top: 2px;
      border-top: 1px solid black;
      border-left: 1px solid black;
    }
  }

  .input {
    display: flex;
    height: 15px;

    .examine {
      flex: 1 1 7%;
      background: #28292c;
    }

    .text {
      flex: 1 1 93%;
      background: #28294c;
    }
  }
}
</style>
